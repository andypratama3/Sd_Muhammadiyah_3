<?php
namespace App\Jobs;

use Exception;
use App\Models\Charge;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Bus\Queueable;
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
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 120, 300]; // 1 min, 2 min, 5 min

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
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

            $categoryName = $charge->kategori_pembayaran->name;
            $monthName = Carbon::now()->locale('id')->translatedFormat('F');

            $siswa = $charge->siswa;
            $kelas = $siswa->kelas->first();
            $grossAmount = intval($charge->gross_amount);
            $namaSiswa = $siswa->name;
            $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
            $noHp = '62' . ltrim($siswa->no_hp ?? '85349734475', '0');

            $publicUrl = null;

            // Jika kategori SPP => buat QR & kirim dengan gambar
            if ($categoryName === 'SPP') {
                // $publicUrl = "https://ansor.sdmuhammadiyah3smd.com/storage/1/qr_code_1.png";
                $publicUrl = $this->generateQrCode($charge, $categoryName, $monthName, $namaSiswa);

                $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                    . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                    . "Tagihan *SPP bulan $monthName* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                    . "📌 Silakan pindai QR Code berikut untuk pembayaran.\n\n"
                    . "Terima kasih atas kerjasamanya.\n"
                    . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

                $this->sendWhatsApp($noHp, $body, $publicUrl);
            }
            elseif ($categoryName === 'DPP') {
                $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                    . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                    . "Tagihan *DPP* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                    . "Silakan lakukan pembayaran di website:\n\n"
                    . "🔗 https://sdmuhammadiyah3smd.com/pembayaran\n\n"
                    . "VA: *$charge->va_number*\n\n"
                    . "Terima kasih atas perhatiannya.\n"
                    . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

                $this->sendWhatsApp($noHp, $body);
            }
            else {
                $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                    . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                    . "Tagihan pembayaran *$categoryName* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                    . "Silakan cek aplikasi atau hubungi pihak sekolah.\n\n"
                    . "Terima kasih.\n"
                    . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

                $this->sendWhatsApp($noHp, $body);
            }

            Log::info('WhatsApp notification sent successfully', [
                'order_id' => $this->orderId,
                'category' => $categoryName,
                'phone' => $noHp,
            ]);

        } catch (Exception $e) {
            Log::error('SendWhatsappJob failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw exception untuk trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Generate QR Code dan return public URL
     */
    private function generateQrCode($charge, $categoryName, $monthName, $namaSiswa): ?string
    {
        try {
            $qrImageUrl = $charge->url_action;

            if (!$qrImageUrl) {
                Log::warning('QR image URL is empty', ['charge_id' => $charge->id]);
                return null;
            }

            $relativePath = 'img/waqr/spp/';
            $storagePath = storage_path('app/public/' . $relativePath);

            // Pastikan folder exists
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
            }

            $fileName = 'qr-siswa-' . Str::slug($categoryName . '-' . $monthName . '-' . $namaSiswa, '-') . '.png';

            ImageHelper::resizeAndSave($qrImageUrl, $storagePath, $fileName, 512, 512);

            return asset('storage/' . $relativePath . $fileName);

        } catch (Exception $e) {
            Log::error('Failed to generate QR code', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send WhatsApp message via WhatsappMetaService
     */
    private function sendWhatsApp(string $target, string $message, ?string $imageUrl = null): void
    {
        try {
            $whatsApp = new WhatsappMetaService();
            $result = $whatsApp->sendMessage($target, $message, $imageUrl);

            if (!$result['success']) {
                Log::warning('WhatsApp send failed', [
                    'target' => $target,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }

        } catch (Exception $e) {
            Log::error('WhatsApp sending error', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            // Re-throw untuk trigger retry
            throw $e;
        }
    }

    /**
     * Handle failed job
     */
    public function failed(Exception $exception)
    {
        Log::critical('SendWhatsappJob permanently failed after all retries', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);

        // Optional: Kirim notifikasi ke admin atau simpan ke database
    }
}
