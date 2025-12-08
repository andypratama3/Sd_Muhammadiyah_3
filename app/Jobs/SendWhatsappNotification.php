<?php

namespace App\Jobs;

use App\Models\Charge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsappMetaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsappNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $orderId;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 60, 300]; // Retry: 10s, 60s, 300s

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        try {
            $whatsApp = new WhatsappMetaService();

            // ✅ Fetch charge dengan relasi
            $charge = Charge::with(['siswa.kelas', 'kategori_pembayaran'])
                ->where('order_id', $this->orderId)
                ->orWhere('id', $this->orderId)
                ->first();

            if (!$charge) {
                Log::warning('Charge not found', [
                    'orderId' => $this->orderId,
                    'job' => self::class,
                ]);
                return;
            }

            // ✅ Extract data dengan null safety
            $categoryName = $charge->kategori_pembayaran?->name ?? 'UNKNOWN';
            $monthName = Carbon::now()->locale('id')->translatedFormat('F');

            $siswa = $charge->siswa;
            if (!$siswa) {
                Log::error('Siswa not found for charge', ['chargeId' => $charge->id]);
                $this->fail(new \Exception('Siswa not found'));
                return;
            }

            $kelas = $siswa->kelas?->first();
            $grossAmount = intval($charge->gross_amount);
            $namaSiswa = $siswa->name;
            $kelasSiswa = $kelas?->name ?? 'Tidak diketahui';
            $noHp = $siswa->no_hp;

            // ✅ Validate phone number
            if (empty($noHp)) {
                Log::error('Phone number is empty', [
                    'chargeId' => $charge->id,
                    'siswaId' => $siswa->id,
                ]);
                $this->fail(new \Exception('Phone number is empty'));
                return;
            }

            Log::info('Processing WhatsApp notification', [
                'orderId' => $this->orderId,
                'category' => $categoryName,
                'phone' => $noHp,
                'amount' => $grossAmount,
            ]);

            // ✅ Send template based on category
            $this->sendTemplateByCategory(
                $whatsApp,
                $categoryName,
                $namaSiswa,
                $kelasSiswa,
                $monthName,
                $grossAmount,
                $noHp,
                $charge
            );

            Log::info('WhatsApp notification sent successfully', [
                'orderId' => $this->orderId,
                'category' => $categoryName,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp Job Failed', [
                'orderId' => $this->orderId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Retry atau fail setelah maksimal attempts
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            }
        }
    }

    /**
     * Send template based on payment category
     */
    private function sendTemplateByCategory(
        WhatsappMetaService $whatsApp,
        string $categoryName,
        string $namaSiswa,
        string $kelasSiswa,
        string $monthName,
        int $grossAmount,
        string $noHp,
        Charge $charge
    ): void {
        $templateName = '';
        $parameters = [];

        switch ($categoryName) {
            case 'SPP':
                $templateName = 'spp_reminder';
                $parameters = [
                    $namaSiswa,
                    $kelasSiswa,
                    $monthName,
                    number_format($grossAmount, 0, ',', '.'),
                ];
                break;

            case 'DPP':
                $templateName = 'dpp_reminder';
                $parameters = [
                    $namaSiswa,
                    $kelasSiswa,
                    number_format($grossAmount, 0, ',', '.'),
                    $charge->va_number ?? 'N/A',
                ];
                break;

            default:
                $templateName = 'general_payment_reminder';
                $parameters = [
                    $namaSiswa,
                    $kelasSiswa,
                    $categoryName,
                    number_format($grossAmount, 0, ',', '.'),
                ];
        }

        // ✅ Send dan check response
        $result = $whatsApp->sendTemplate($noHp, $templateName, $parameters);

        if (!$result['success'] ?? false) {
            throw new \Exception(
                'Failed to send WhatsApp: ' . json_encode($result['error'] ?? 'Unknown error')
            );
        }

        Log::debug('Template sent', [
            'template' => $templateName,
            'phone' => $noHp,
            'parameters' => count($parameters),
        ]);
    }

    /**
     * Handle job failed
     */
    public function failed(\Throwable $exception)
    {
        Log::critical('WhatsApp Job permanently failed', [
            'orderId' => $this->orderId,
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);

        // ✅ Optional: Send alert to admin
        // Notification::route('mail', 'admin@example.com')
        //     ->notify(new WhatsappJobFailedNotification($this->orderId, $exception));
    }
}
