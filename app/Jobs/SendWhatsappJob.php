<?php

namespace App\Jobs;

use App\Models\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\ImageHelper;
use App\Services\WhatsAppService;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $charge = Charge::with(['siswa.kelas', 'kategori_pembayaran'])
            ->where(function ($query) {
                $query->where('order_id', $this->orderId)
                    ->orWhere('id', $this->orderId);
            })
            ->first();

        if (! $charge) return;

        $categoryname = $charge->kategori_pembayaran->name;
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $siswa = $charge->siswa;
        $kelas = $siswa->kelas->first();
        $grossAmount = intval($charge->gross_amount);
        $namaSiswa = $siswa->name;
        $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
        $noHp = '62' . ltrim($siswa->no_hp ?? '85349734475', '0');

        $wa = new WhatsAppService();
        $publicUrl = null;

        if ($categoryname === 'SPP') {
            $qrImageUrl = $charge->url_action;
            $folderPath = public_path('storage/img/waqr/spp');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            $fileName = 'qr-siswa-' . Str::slug($categoryname . '-' . $monthName . '-' . $namaSiswa, '-') . '.png';
            $relativePath = 'img/waqr/spp/';
            $storagePath = storage_path('app/public/' . $relativePath);
            ImageHelper::resizeAndSave($qrImageUrl, $storagePath, $fileName, 512, 512);
            $publicUrl = asset('storage/' . $relativePath . $fileName);

            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                . "Tagihan *SPP bulan $monthName* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "📌 Silakan pindai QR Code berikut untuk pembayaran.\n\n"
                . "Terima kasih atas kerjasamanya.\n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $wa->sendMessage($noHp, $body, $publicUrl);
        }
        elseif ($categoryname === 'DPP') {
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                . "Tagihan *DPP* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "Silakan lakukan pembayaran melalui metode yang telah disediakan pada Website:\n\n"
                . "🔗 https://sdmuhammadiyah3smd.com/pembayaran\n\n"
                . "Metode Virtual Account:\n"
                . "- Bank Permata VA: *$charge->va_number*\n\n"
                . "Terima kasih atas perhatian dan kerjasamanya.\n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $wa->sendMessage($noHp, $body);
        }
        else {
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),\n\n"
                . "Kami informasikan terdapat tagihan pembayaran *$categoryname* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "Silakan cek aplikasi atau hubungi pihak sekolah untuk info lebih lanjut.\n\n"
                . "Terima kasih atas perhatian dan kerjasamanya.\n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $wa->sendMessage($noHp, $body);
        }
    }
}
