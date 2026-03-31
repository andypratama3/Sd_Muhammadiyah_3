<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppMessage;
use App\Services\WhatsAppBotService;
use App\Services\WhatsappMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppBotService $botService
    ) {}

    /**
     * Verify webhook token from Meta
     * GET /api/v1/webhook/whatsapp
     */
    public function verify(Request $request)
    {
        try {
            $mode      = $request->get('hub_mode');
            $token     = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            Log::channel('whatsapp')->info('Webhook verify request', [
                'mode'        => $mode,
                'token_match' => $token === config('services.whatsapp.webhook_verify_token'),
            ]);

            $verifyToken = config('services.whatsapp.webhook_verify_token');

            if (!$verifyToken) {
                Log::channel('whatsapp')->error('❌ Verify token not configured');
                return response('Verify token not configured', 500);
            }

            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::channel('whatsapp')->info('✅ Webhook verified successfully');
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }

            Log::channel('whatsapp')->error('❌ Webhook verification failed');
            return response('Forbidden', 403);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Verify exception', ['error' => $e->getMessage()]);
            return response('Error', 500);
        }
    }

    /**
     * Handle incoming webhooks from Meta
     * POST /api/v1/webhook/whatsapp
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->json()->all();

            Log::channel('whatsapp')->info('📨 Webhook received', [
                'object' => $data['object'] ?? null,
            ]);

            if (!isset($data['object']) || $data['object'] !== 'whatsapp_business_account') {
                Log::channel('whatsapp')->warning('⚠️ Invalid webhook object');
                return response()->json(['status' => 'ignored'], 200);
            }

            if (!isset($data['entry']) || !is_array($data['entry'])) {
                return response()->json(['status' => 'ok'], 200);
            }

            foreach ($data['entry'] as $entry) {
                $this->processEntry($entry);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Webhook handle error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);

            // Selalu return 200 agar Meta tidak retry
            return response()->json(['status' => 'error'], 200);
        }
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    private function processEntry($entry): void
    {
        if (!isset($entry['changes']) || !is_array($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? [];

            match ($field) {
                'messages'                      => $this->processMessages($value),
                'message_template_status_update' => $this->processTemplateStatusUpdate($value),
                'account_alerts'                 => $this->processAccountAlerts($value),
                'account_update'                 => Log::channel('whatsapp')->info('🔄 Account update', $value),
                default                          => Log::channel('whatsapp')->debug('Ignoring field', ['field' => $field]),
            };

            // Handle delivery receipts
            if (!empty($change['statuses'])) {
                $this->processDeliveryReceipts($change['statuses'], $value);
            }
        }
    }

    private function processMessages($value): void
    {
        // Handle incoming messages
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($message, $value);
            }
        }

        // Handle status updates (sent, delivered, read, failed)
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleMessageStatus($status);
            }
        }
    }

    private function handleIncomingMessage($message, $value): void
    {
        try {
            $from      = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? now()->timestamp;
            $type      = $message['type'] ?? 'unknown';

            if (!$from || !$messageId) {
                Log::channel('whatsapp')->warning('⚠️ Invalid message data');
                return;
            }

            $profileName = $value['contacts'][0]['profile']['name'] ?? 'Bapak/Ibu';

             // ✅ TAMBAHKAN INI — Filter nomor provider / operator
            if ($this->isBlockedSender($from, $profileName)) {
                Log::channel('whatsapp')->info('🚫 Ignored: blocked sender', [
                    'from' => substr($from, 0, 4) . '****',
                    'name' => $profileName,
                ]);
                return; 
            }

            Log::channel('whatsapp')->info('📬 Incoming message', [
                'from' => substr($from, 0, 4) . '****',
                'name' => $profileName,
                'type' => $type,
            ]);

            // Simpan pesan masuk ke database
            $this->storeIncomingMessage([
                'message_id'   => $messageId,
                'phone'        => $from,
                'profile_name' => $profileName,
                'type'         => $type,
                'content'      => json_encode($message),
                'timestamp'    => date('Y-m-d H:i:s', $timestamp),
            ]);

            // Update status HP siswa ketika ada balasan
            $this->updateSiswaPhoneStatus($from);

            // ================================================================
            // ROUTING KE BOT SERVICE
            // Hanya proses tipe pesan yang bisa ditangani bot
            // ================================================================
            $messageText = $this->extractMessageText($message, $type);

            if ($messageText !== null) {
                // Jalankan bot secara async-like menggunakan queue jika tersedia
                // atau langsung (synchronous) jika tidak ada queue
                $this->dispatchBotHandler($from, $messageText, $profileName);
            } else {
                // Tipe pesan tidak didukung (gambar, video, audio, dll)
                $this->handleUnsupportedMessageType($from, $type, $profileName);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Handle incoming message error', [
                'error'     => $e->getMessage(),
                'messageId' => $messageId ?? null,
            ]);
        }
    }

    /**
     * Ekstrak teks dari berbagai tipe pesan
     * Return null jika tipe tidak didukung
     */
    private function extractMessageText($message, string $type): ?string
    {
        return match ($type) {
            'text'        => trim($message['text']['body'] ?? ''),
            'interactive' => $message['interactive']['button_reply']['id']
                            ?? $message['interactive']['list_reply']['id']
                            ?? null,
            'button'      => $message['button']['payload']
                            ?? $message['button']['text']
                            ?? null,
            default       => null, // image, audio, video, document, dll → tidak didukung
        };
    }

    /**
     * Dispatch ke bot handler
     * Menggunakan queue job jika tersedia, fallback ke synchronous
     */
    private function dispatchBotHandler(string $phone, string $messageText, string $profileName): void
    {
        // Cek apakah ada queue job tersedia
        // Jika ingin async, buat App\Jobs\ProcessWhatsAppMessage dan uncomment di bawah:
        //
        ProcessWhatsAppMessage::dispatch($phone, $messageText, $profileName);
        //
        // Untuk sekarang: synchronous (langsung proses di sini)

        try {
            $this->botService->handle($phone, $messageText, $profileName);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Bot handler error', [
                'error' => $e->getMessage(),
                'phone' => substr($phone, 0, 4) . '****',
            ]);
        }
    }

    /**
     * Kirim pesan jika user mengirim tipe yang tidak didukung (gambar, video, dll)
     */
    private function handleUnsupportedMessageType(string $from, string $type, string $profileName): void
    {
        Log::channel('whatsapp')->info("📎 Unsupported type: {$type}", ['from' => substr($from, 0, 4) . '****']);

        try {
            $whatsapp = new WhatsappMetaService();
            $whatsapp->sendMessage(
                $from,
                "Maaf, saya hanya dapat memproses pesan teks. 🙏\n\n"
                . "Silakan kirimkan *NISN* (10 digit angka) untuk mengecek informasi tagihan.\n\n"
                . "Ketik *bantuan* untuk panduan penggunaan."
            );
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Unsupported type reply error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update status HP siswa ketika ada balasan pesan
     */
    private function updateSiswaPhoneStatus(string $phoneNumber): void
    {
        try {
            $formattedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            if (strpos($formattedPhone, '62') === 0) {
                $formattedPhone = '0' . substr($formattedPhone, 2);
            }

            $updated = DB::table('siswas')
                ->where('no_hp', $formattedPhone)
                ->update([
                    'status_hp'             => 1,
                    'status_hp_verified_at' => now(),
                    'updated_at'            => now(),
                ]);

            if ($updated > 0) {
                Log::channel('whatsapp')->info('✅ Siswa phone status updated', [
                    'phone'        => $formattedPhone,
                    'updated_rows' => $updated,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Update siswa phone status error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleMessageStatus($status): void
    {
        try {
            $messageId  = $status['id'] ?? null;
            $statusType = $status['status'] ?? null;
            $recipient  = $status['recipient_id'] ?? null;
            $timestamp  = $status['timestamp'] ?? now()->timestamp;

            if (!$messageId || !$statusType) return;

            Log::channel('whatsapp')->info('📊 Status update', [
                'messageId' => substr($messageId, -10),
                'status'    => $statusType,
            ]);

            $this->storeMessageStatus([
                'message_id' => $messageId,
                'status'     => $statusType,
                'recipient'  => $recipient,
                'timestamp'  => date('Y-m-d H:i:s', $timestamp),
                'errors'     => isset($status['errors']) ? json_encode($status['errors']) : null,
            ]);

            if ($statusType === 'failed') {
                Log::channel('whatsapp')->error('❌ Message failed', ['errors' => $status['errors'] ?? []]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Status update error', ['error' => $e->getMessage()]);
        }
    }

    private function processDeliveryReceipts($statuses, $value): void
    {
        foreach ($statuses as $status) {
            $this->handleMessageStatus($status);
        }
    }

    private function processTemplateStatusUpdate($value): void
    {
        Log::channel('whatsapp')->info('📋 Template status update', [
            'event'    => $value['event'] ?? null,
            'template' => $value['message_template_name'] ?? null,
        ]);
    }

    private function processAccountAlerts($value): void
    {
        $alertType = $value['alert_type'] ?? null;
        Log::channel('whatsapp')->warning('🚨 Account alert', ['type' => $alertType]);

        if (in_array($alertType, ['PHONE_NUMBER_FLAGGED', 'PHONE_NUMBER_RESTRICTED'])) {
            Log::channel('whatsapp')->error('🚨 CRITICAL ALERT', ['type' => $alertType, 'details' => $value]);
        }
    }

    private function storeIncomingMessage(array $data): void
    {
        try {
            DB::table('whatsapp_incoming_messages')->insert([
                'message_id'   => $data['message_id'],
                'phone'        => $data['phone'],
                'profile_name' => $data['profile_name'],
                'type'         => $data['type'],
                'content'      => $data['content'],
                'status'       => 'received',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store message error', ['error' => $e->getMessage()]);
        }
    }

    private function storeMessageStatus(array $data): void
    {
        try {
            DB::table('whatsapp_message_statuses')->insert([
                'message_id' => $data['message_id'],
                'status'     => $data['status'],
                'recipient'  => $data['recipient'],
                'timestamp'  => $data['timestamp'],
                'errors'     => $data['errors'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store status error', ['error' => $e->getMessage()]);
        }
    }

    private function isBlockedSender(string $phone, string $profileName = ''): bool
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        // ── 1. Shortcode: nomor ≤ 7 digit (3636, 1414, 185, dll.) ────────────
        if (strlen($cleaned) <= 7) {
            return true;
        }

        // ── 2. Nomor provider Indonesia yang dikenal ──────────────────────────
        $blockedNumbers = [
            // Telkomsel
            '6281100008080', '6208001000800', '62811000',
            // Indosat / IM3 / Mentari
            '6285500185',    '6285500186',    '6285500000185',
            // XL / Axis
            '6281700817',    '6281789817',
            // Tri
            '628985001234',
            // Smartfren
            '628817777333',
        ];

        foreach ($blockedNumbers as $blocked) {
            if (str_starts_with($cleaned, $blocked)) {
                return true;
            }
        }

        // ── 3. Prefix nomor layanan & call center umum ────────────────────────
        $blockedPrefixes = [
            '6214',   // 14xxx — call center Indonesia (1400x, 1414, 14000, dll.)
            '6215',   // 15xxx — call center Indonesia (1500x, dll.)
            '1650',   // US automated sender
            '1844', '1855', '1866', '1877', '1888', // US toll-free automated
        ];

        foreach ($blockedPrefixes as $prefix) {
            if (str_starts_with($cleaned, $prefix)) {
                return true;
            }
        }

        // ── 4. Display name mengandung kata kunci provider / sistem ───────────
        if ($profileName !== '') {
            $lowerName = mb_strtolower(trim($profileName));

            $blockedNames = [
                'telkomsel', 'indosat', 'ooredoo', 'xl axiata', 'xl ', 'axis',
                'smartfren', 'tri indonesia', '3 indonesia', 'by.u', 'byu',
                'whatsapp',  'meta', 'facebook',
                'tokopedia', 'shopee', 'lazada', 'blibli',
                'gojek',     'grab',  'maxim',
                'bca',  'bni', 'bri', 'mandiri', 'bsi', 'danamon', 'cimb',
                'ovo',  'dana', 'linkaja', 'gopay', 'shopeepay',
                'pln',  'bpjs', 'samsat',
            ];

            foreach ($blockedNames as $kw) {
                if (str_contains($lowerName, $kw)) {
                    return true;
                }
            }
        }

        return false;
    }

    // =========================================================================
    // UTILITY ENDPOINTS (tetap ada)
    // =========================================================================

    public function test(Request $request)
    {
        $whatsApp = new WhatsappMetaService();
        $phone    = $request->phone;
        $image    = "https://dashboard.sdmuhammadiyah3smd.com/storage/img/waqr/spp/qr-siswa-spp-juli-abdillah-abqari-agam.png";

        $result = $whatsApp->sendTemplate($phone, 'spp_reminder', [
            'User Test', 'Kelas 2', 'Januari', '20.000'
        ], $image);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function getMessagesHistory(Request $request)
    {
        $limit    = $request->input('limit', 50);
        $whatsapp = new WhatsappMetaService();
        $result   = $whatsapp->getMessagesHistory($limit);

        return response()->json($result);
    }

    public function getTemplate()
    {
        try {
            $whatsapp  = new WhatsappMetaService();
            $templates = $whatsapp->getTemplates();
            return response()->json($templates);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}