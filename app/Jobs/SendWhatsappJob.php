<?php

namespace App\Jobs;

use Exception;
use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsappMetaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Backoff (seconds) between retries.
     */
    public $backoff = [60, 120, 300];

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Main job handler
     */
    public function handle()
    {
        // small throttle protection
        sleep(1);

        try {
            $charge = Charge::with(['siswa.kelas', 'kategori_pembayaran'])
                ->where(function ($query) {
                    $query->where('order_id', $this->orderId)
                          ->orWhere('id', $this->orderId);
                })
                ->first();

            if (!$charge) {
                Log::warning('Charge not found for SendWhatsappJob', ['order_id' => $this->orderId]);
                return;
            }

            // prepare data
            $categoryName = $charge->kategori_pembayaran->name ?? 'UNKNOWN';
            $monthName = Carbon::now()->locale('id')->translatedFormat('F');

            $siswa = $charge->siswa;
            $kelas = optional($siswa->kelas->first())->name ?? 'Tidak diketahui';
            $grossAmount = intval($charge->gross_amount ?? 0);
            $namaSiswa = $siswa->name ?? 'Orang tua';
            $noHpRaw = $siswa->no_hp ?? null;

            if (!$noHpRaw) {
                Log::warning('No phone number for student', ['charge_id' => $charge->id]);
                return;
            }

            // normalize phone to '62...' format
            $noHp = $this->normalizePhone($noHpRaw);

            // choose action based on category
            $result = null;

            if (strtoupper($categoryName) === 'SPP') {
                // generate QR (if needed) and use template with header image
                $qrUrl = $this->generateQrCode($charge, $categoryName, $monthName, $namaSiswa);
                $result = $this->sendWhatsAppTemplate($charge, $noHp, $qrUrl);
            } elseif (strtoupper($categoryName) === 'DPP') {
                $result = $this->sendWhatsAppTemplate($charge, $noHp);
            } else {
                $result = $this->sendWhatsAppTemplate($charge, $noHp);
            }

            Log::info('WhatsApp notification result', [
                'order_id' => $this->orderId,
                'category' => $categoryName,
                'phone' => $noHp,
                'result' => $result
            ]);

            // optional: track result failure for retries (handled by $tries/backoff)
            if (is_array($result) && ($result['success'] ?? false) === false) {
                Log::warning('WhatsApp template send unsuccessful', [
                    'order_id' => $this->orderId,
                    'error' => $result['error'] ?? null,
                    'status' => $result['status'] ?? null
                ]);
                // throw to trigger retry if desired
                throw new Exception('WhatsApp failed: ' . json_encode($result['error'] ?? $result));
            }

        } catch (Exception $e) {
            Log::error('SendWhatsappJob failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // allow job retry mechanism
            throw $e;
        }
    }

    /**
     * Generate QR Code image (save local public storage) and return public URL.
     * Returns null on failure.
     */
    private function generateQrCode($charge, $categoryName, $monthName, $namaSiswa): ?string
    {
        try {
            $qrImageUrl = $charge->url_action; // source URL from charge (remote)

            if (!$qrImageUrl) {
                Log::warning('QR image URL is empty', ['charge_id' => $charge->id]);
                return null;
            }

            $relativePath = 'img/waqr/spp/';
            $storagePath = storage_path('app/public/' . $relativePath);

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
            }

            $fileName = 'qr-siswa-' . Str::slug($categoryName . '-' . $monthName . '-' . $namaSiswa, '-') . '.png';

            // ImageHelper should download/resize/save
            ImageHelper::resizeAndSave($qrImageUrl, $storagePath, $fileName, 512, 512);

            return asset('storage/' . $relativePath . $fileName);
        } catch (Exception $e) {
            Log::error('Failed to generate QR code', [
                'charge_id' => $charge->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send template via WhatsappMetaService based on category mapping
     */
    private function sendWhatsAppTemplate($charge, string $phone, ?string $imageUrl = null): array
    {
        $whatsapp = new WhatsappMetaService();

        $nama = $charge->siswa->name ?? 'Orang Tua';
        $kelas = optional($charge->siswa->kelas->first())->name ?? 'Tidak diketahui';
        $kategori = $charge->kategori_pembayaran->name ?? 'Pembayaran';
        $gross = number_format(intval($charge->gross_amount ?? 0), 0, ',', '.');
        $bulan = Carbon::now()->locale('id')->translatedFormat('F');

        // map category -> template
        $kategoriUpper = strtoupper($kategori);

        if ($kategoriUpper === 'SPP') {
            return $whatsapp->sendTemplate(
                phone: $phone,
                templateName: 'spp_reminder',
                parameters: [$nama, $kelas, $bulan, $gross],
                imageUrl: $imageUrl // header image if present
            );
        }

        if ($kategoriUpper === 'DPP') {
            return $whatsapp->sendTemplate(
                phone: $phone,
                templateName: 'dpp_reminder',
                parameters: [$nama, $kelas, $gross, ($charge->va_number ?? '-')],
                imageUrl: null
            );
        }

        // fallback general reminder
        return $whatsapp->sendTemplate(
            phone: $phone,
            templateName: 'general_payment_reminder',
            parameters: [$nama, $kelas, $kategori, $gross],
            imageUrl: null
        );
    }

    /**
     * Normalize phone numbers into 62... format (no leading +).
     * Accepts numbers like '0851...', '+62851...', '62851...'
     */
    private function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/\D+/', '', $phone);

        // if starts with '0' => replace with 62
        if (strpos($clean, '0') === 0) {
            $clean = '62' . substr($clean, 1);
        }

        // if starts with '62' already OK; if starts with '8' prefix with 62
        if (strpos($clean, '62') === 0) {
            return $clean;
        }

        if (strpos($clean, '8') === 0) {
            return '62' . $clean;
        }

        // fallback: return cleaned prefixed with 62
        return '62' . $clean;
    }

    /**
     * Called when the job finally fails after all retries
     */
    public function failed(Exception $exception)
    {
        Log::critical('SendWhatsappJob permanently failed after retries', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // optional: notify admin, save to DB, etc.
    }
}
