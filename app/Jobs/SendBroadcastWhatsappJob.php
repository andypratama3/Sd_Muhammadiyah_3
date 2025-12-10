<?php

namespace App\Jobs;

use Exception;
use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\WhatsappMetaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendBroadcastWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignId;
    protected $siswaId;
    protected $message;
    protected $kelasId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 120, 300]; // 1 min, 2 min, 5 min

    /**
     * Constructor
     * @param int $campaignId - ID dari broadcast campaign
     * @param int $siswaId - ID siswa yang akan menerima
     * @param string $message - Pesan yang akan dikirim
     * @param int|null $kelasId - ID kelas (untuk reference)
     */
    public function __construct($campaignId, $siswaId, $message, $kelasId = null)
    {
        $this->campaignId = $campaignId;
        $this->siswaId = $siswaId;
        $this->message = $message;
        $this->kelasId = $kelasId;
    }

    public function handle()
    {
        sleep(1); // Prevent burst

        try {
            // ✅ STEP 1: GET SISWA DATA
            $siswa = Siswa::with('kelas')->find($this->siswaId);

            if (!$siswa) {
                Log::warning('Siswa not found for broadcast', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $this->siswaId,
                ]);
                $this->recordRecipientStatus('skipped', 'Siswa not found');
                return; // Don't retry
            }

            // ✅ STEP 2: VALIDASI NO HP
            if (empty($siswa->no_hp)) {
                Log::warning('Siswa has no phone number', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $siswa->id,
                    'siswa_name' => $siswa->name,
                ]);
                $this->recordRecipientStatus('skipped', 'No phone number');
                return; // Don't retry
            }

            // ✅ STEP 3: FORMAT NOMOR HP
            $noHp = '62' . ltrim($siswa->no_hp, '0');

            // ✅ STEP 4: CEK CONSENT
            if (!$this->checkConsent($noHp)) {
                Log::info('Phone opted out, skipping', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $siswa->id,
                    'phone' => $this->maskPhone($noHp),
                ]);
                $this->recordRecipientStatus('skipped', 'Phone opted out');
                return; // Don't retry
            }

            // ✅ STEP 5: CEK DUPLIKASI (IDEMPOTENCY)
            $idempotencyKey = $this->generateIdempotencyKey($siswa->id, $this->message);

            if ($this->isDuplicate($idempotencyKey)) {
                Log::info('Broadcast already sent to this siswa', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $siswa->id,
                ]);
                $this->recordRecipientStatus('sent', 'Already sent (duplicate)');
                return; // Don't retry
            }

            // ✅ STEP 6: CREATE BROADCAST RECIPIENT RECORD
            $recipientId = $this->createBroadcastRecipient($siswa, $noHp);

            // ✅ STEP 7: KIRIM MESSAGE
            $whatsapp = new WhatsappMetaService();
            $result = $whatsapp->sendMessage($noHp, $this->message, null, $idempotencyKey);

            // ✅ STEP 8: PROCESS RESULT
            if (!$result['success']) {
                Log::warning('Broadcast send failed', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $siswa->id,
                    'attempt' => $this->attempts(),
                    'error' => $result['error'] ?? 'Unknown',
                ]);

                // ✅ UPDATE RECIPIENT STATUS
                $this->updateBroadcastRecipient($recipientId, 'failed', $result);

                // ✅ RECORD DETAILED LOG
                $this->recordBroadcastLog(
                    $recipientId,
                    $siswa,
                    $noHp,
                    'failed',
                    $result
                );

                // Throw untuk trigger retry
                throw new Exception('WhatsApp send failed: ' . json_encode($result));
            }

            // ✅ STEP 9: SUCCESS - UPDATE RECIPIENT
            $this->updateBroadcastRecipient(
                $recipientId,
                'sent',
                [
                    'message_id' => $result['message_id'] ?? null,
                    'success' => true,
                ]
            );

            // ✅ STEP 10: RECORD DETAILED LOG
            $this->recordBroadcastLog(
                $recipientId,
                $siswa,
                $noHp,
                'sent',
                $result
            );

            // ✅ STEP 11: UPDATE CAMPAIGN STATS
            $this->updateCampaignStats('sent');

            Log::info('Broadcast message sent to siswa', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $siswa->id,
                'siswa_name' => $siswa->name,
                'phone' => $this->maskPhone($noHp),
                'message_id' => $result['message_id'] ?? null,
            ]);

        } catch (Exception $e) {
            Log::error('SendBroadcastWhatsappJob error', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $this->siswaId,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'error' => $e->getMessage(),
            ]);

            // ✅ JIKA SUDAH RETRY 3x, JANGAN THROW
            if ($this->attempts() >= $this->tries) {
                Log::critical('Broadcast permanently failed for siswa', [
                    'campaign_id' => $this->campaignId,
                    'siswa_id' => $this->siswaId,
                    'attempts' => $this->attempts(),
                ]);

                // Record final failure
                $siswa = Siswa::find($this->siswaId);
                if ($siswa) {
                    $noHp = '62' . ltrim($siswa->no_hp, '0');
                    $this->recordBroadcastLog(
                        null,
                        $siswa,
                        $noHp,
                        'failed',
                        [
                            'error' => $e->getMessage(),
                            'final_attempt' => true,
                        ]
                    );

                    // Update recipient ke final status
                    DB::table('whatsapp_broadcast_recipients')
                        ->where('campaign_id', $this->campaignId)
                        ->where('siswa_id', $this->siswaId)
                        ->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                            'attempt_count' => $this->attempts(),
                            'updated_at' => now(),
                        ]);

                    // Update campaign stats
                    $this->updateCampaignStats('failed');
                }

                return; // Let job die gracefully
            }

            // Throw untuk trigger retry (attempt < 3)
            throw $e;
        }
    }

    /**
     * ✅ CREATE BROADCAST RECIPIENT RECORD
     */
    private function createBroadcastRecipient(Siswa $siswa, string $phone): int
    {
        try {
            DB::table('whatsapp_broadcast_recipients')->insert([
                'campaign_id' => $this->campaignId,
                'siswa_id' => $siswa->id,
                'phone' => $phone,
                'status' => 'pending',
                'attempt_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get the ID yang baru dibuat
            $recipient = DB::table('whatsapp_broadcast_recipients')
                ->where('campaign_id', $this->campaignId)
                ->where('siswa_id', $siswa->id)
                ->where('phone', $phone)
                ->first();

            return $recipient->id;

        } catch (Exception $e) {
            Log::error('Failed to create broadcast recipient', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * ✅ UPDATE BROADCAST RECIPIENT STATUS
     */
    private function updateBroadcastRecipient(
        ?int $recipientId,
        string $status,
        array $data = []
    ): void {
        try {
            if (!$recipientId) {
                return; // Skip jika tidak ada recipient ID
            }

            DB::table('whatsapp_broadcast_recipients')
                ->where('id', $recipientId)
                ->update([
                    'status' => $status,
                    'message_id' => $data['message_id'] ?? null,
                    'attempt_count' => DB::raw('attempt_count + 1'),
                    'error_message' => isset($data['error']) ? $data['error'] : null,
                    'sent_at' => ($status === 'sent') ? now() : null,
                    'updated_at' => now(),
                ]);

        } catch (Exception $e) {
            Log::error('Failed to update broadcast recipient', [
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ✅ RECORD BROADCAST LOG (DETAILED)
     */
    private function recordBroadcastLog(
        ?int $recipientId,
        Siswa $siswa,
        string $phone,
        string $status,
        array $data = []
    ): void {
        try {
            // Get siswa kelas
            $siswaKelas = $siswa->kelas()->first();
            $kelasId = $siswaKelas ? $siswaKelas->id : $this->kelasId;

            // Generate idempotency key
            $idempotencyKey = $this->generateIdempotencyKey($siswa->id, $this->message);

            DB::table('whatsapp_broadcast_logs')->insert([
                'campaign_id' => $this->campaignId,
                'recipient_id' => $recipientId,
                'siswa_id' => $siswa->id,
                'kelas_id' => $kelasId,
                'phone' => $phone,
                'message_preview' => substr($this->message, 0, 255),
                'status' => $status,
                'message_id' => $data['message_id'] ?? null,
                'http_status' => $data['status'] ?? null,
                'error_code' => $this->extractErrorCode($data),
                'error_message' => $this->extractErrorMessage($data),
                'api_response' => isset($data['data']) ? json_encode($data['data']) : (
                    isset($data['error']) ? json_encode($data['error']) : null
                ),
                'attempt_number' => $this->attempts(),
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Cache untuk prevent duplikat
            Cache::put(
                "broadcast_msg_{$idempotencyKey}",
                true,
                now()->addDay()
            );

        } catch (Exception $e) {
            Log::error('Failed to record broadcast log', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $siswa->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - continue even if logging fails
        }
    }

    /**
     * ✅ UPDATE CAMPAIGN STATISTICS
     */
    private function updateCampaignStats(string $type): void
    {
        try {
            $updateData = [];

            if ($type === 'sent') {
                $updateData['total_sent'] = DB::raw('total_sent + 1');
            } elseif ($type === 'failed') {
                $updateData['total_failed'] = DB::raw('total_failed + 1');
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();

                DB::table('whatsapp_broadcast_campaigns')
                    ->where('id', $this->campaignId)
                    ->update($updateData);
            }

        } catch (Exception $e) {
            Log::error('Failed to update campaign stats', [
                'campaign_id' => $this->campaignId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ✅ RECORD RECIPIENT STATUS (Simple - untuk skipped items)
     */
    private function recordRecipientStatus(string $status, string $reason): void
    {
        try {
            DB::table('whatsapp_broadcast_recipients')->insert([
                'campaign_id' => $this->campaignId,
                'siswa_id' => $this->siswaId,
                'phone' => 'UNKNOWN',
                'status' => $status,
                'error_message' => $reason,
                'attempt_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($status === 'skipped') {
                DB::table('whatsapp_broadcast_campaigns')
                    ->where('id', $this->campaignId)
                    ->update(['total_skipped' => DB::raw('total_skipped + 1')]);
            }

        } catch (Exception $e) {
            Log::error('Failed to record recipient status', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $this->siswaId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ✅ CEK CONSENT
     */
    private function checkConsent(string $phone): bool
    {
        $consent = DB::table('whatsapp_broadcast_consents')
            ->where('phone', $phone)
            ->first();

        // Default allow jika tidak ada record
        if (!$consent) {
            return true;
        }

        return (bool) $consent->opted_in;
    }

    /**
     * ✅ GENERATE IDEMPOTENCY KEY
     */
    private function generateIdempotencyKey(int $siswaId, string $message): string
    {
        return hash('sha256', $siswaId . '|' . md5($message));
    }

    /**
     * ✅ CEK DUPLIKASI
     */
    private function isDuplicate(string $idempotencyKey): bool
    {
        // Check cache dulu (fastest)
        if (Cache::has("broadcast_msg_{$idempotencyKey}")) {
            return true;
        }

        // Check database
        $log = DB::table('whatsapp_broadcast_logs')
            ->where('idempotency_key', $idempotencyKey)
            ->where('status', 'sent')
            ->first();

        return (bool) $log;
    }

    /**
     * ✅ EXTRACT ERROR CODE
     */
    private function extractErrorCode(array $data): ?string
    {
        if (isset($data['status']) && is_numeric($data['status'])) {
            return (string) $data['status'];
        }

        if (isset($data['error'])) {
            if (is_array($data['error']) && isset($data['error']['code'])) {
                return (string) $data['error']['code'];
            }
            if (is_string($data['error'])) {
                return $data['error'];
            }
        }

        return null;
    }

    /**
     * ✅ EXTRACT ERROR MESSAGE
     */
    private function extractErrorMessage(array $data): ?string
    {
        if (isset($data['error'])) {
            if (is_array($data['error'])) {
                return $data['error']['message'] ?? json_encode($data['error']);
            }
            return (string) $data['error'];
        }

        if (isset($data['message'])) {
            return (string) $data['message'];
        }

        return null;
    }

    /**
     * ✅ MASK PHONE UNTUK PRIVACY
     */
    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 2) . '***' . substr($phone, -4);
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception)
    {
        Log::critical('SendBroadcastWhatsappJob permanently failed', [
            'campaign_id' => $this->campaignId,
            'siswa_id' => $this->siswaId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Update recipient status ke failed
        try {
            DB::table('whatsapp_broadcast_recipients')
                ->where('campaign_id', $this->campaignId)
                ->where('siswa_id', $this->siswaId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'updated_at' => now(),
                ]);

            // Update campaign stats
            DB::table('whatsapp_broadcast_campaigns')
                ->where('id', $this->campaignId)
                ->update([
                    'total_failed' => DB::raw('total_failed + 1'),
                    'updated_at' => now(),
                ]);

        } catch (Exception $e) {
            Log::error('Failed to handle job failure', [
                'campaign_id' => $this->campaignId,
                'siswa_id' => $this->siswaId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
