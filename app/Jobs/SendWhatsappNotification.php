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

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $whatsApp = new WhatsappMetaService();

        $charge = Charge::with(['siswa.kelas', 'kategori_pembayaran'])
            ->where('order_id', $this->orderId)
            ->orWhere('id', $this->orderId)
            ->first();

        if (!$charge) {
            Log::warning('Charge not found', ['orderId' => $this->orderId]);
            return;
        }

        $categoryname = $charge->kategori_pembayaran->name;
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $siswa = $charge->siswa;
        $kelas = $siswa->kelas->first();
        $grossAmount = intval($charge->gross_amount);
        $namaSiswa = $siswa->name;
        $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
        $noHp = $siswa->no_hp ?? '85349734475';

        if ($categoryname === 'SPP') {
            // Send SPP template
            $parameters = [
                $namaSiswa,
                $kelasSiswa,
                $monthName,
                number_format($grossAmount, 0, ',', '.'),
            ];
            $whatsApp->sendTemplate($noHp, 'spp_reminder', $parameters);

        } elseif ($categoryname === 'DPP') {
            // Send DPP template
            $parameters = [
                $namaSiswa,
                $kelasSiswa,
                number_format($grossAmount, 0, ',', '.'),
                $charge->va_number ?? 'N/A',
            ];
            $whatsApp->sendTemplate($noHp, 'dpp_reminder', $parameters);

        } else {
            // Send general template
            $parameters = [
                $namaSiswa,
                $kelasSiswa,
                $categoryname,
                number_format($grossAmount, 0, ',', '.'),
            ];
            $whatsApp->sendTemplate($noHp, 'general_payment_reminder', $parameters);
        }

        Log::info('WhatsApp sent', ['orderId' => $this->orderId]);
    }
}
