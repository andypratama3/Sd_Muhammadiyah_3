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
use Twilio\Rest\Client as TwilioClient;
use App\Helpers\ImageHelper;

class SendWhatsappNotification implements ShouldQueue
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
        $noHp = '+62' . ltrim($siswa->no_hp ?? '85349734475', '0');

        $publicUrl = null;

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $whatsappFrom = env('TWILIO_WHATSAPP_FROM');
        $client = new TwilioClient($sid, $token);

        if ($categoryname === 'SPP') {
            $qrImageUrl = $charge->url_action;
            $fileName = 'qr-siswa-' . Str::slug($categoryname . '-' . $monthName . '-' . $namaSiswa, '-') . '.png';
            $relativePath = 'img/waqr/spp/';
            $storagePath = storage_path('app/public/' . $relativePath);
            ImageHelper::resizeAndSave($qrImageUrl, $storagePath, $fileName, 512, 512);
            $publicUrl = asset('storage/' . $relativePath . $fileName);

            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),  \n\n"
                . "Tagihan *SPP bulan $monthName* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "📌 Silakan pindai QR Code berikut untuk pembayaran.\n\n"
                . "Terima kasih atas kerjasamanya. \n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $client->messages->create('whatsapp:' . $noHp, [
                'from' => $whatsappFrom,
                'body' => $body,
                'mediaUrl' => [$publicUrl],
            ]);
        }
        else if ($categoryname === 'DPP') {
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),  \n\n"
                . "Tagihan *DPP* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "Silakan lakukan pembayaran melalui metode yang telah disediakan Pada Website.\n\n"
                . "https://sdmuhammadiyah3smd.com/pembayaran \n\n"
                . "atau menggunakan menggunakan metode Virtual Account.\n\n"
                . "Menggunakan Virtual Account : \n"
                . "- Bank Permata Virtual Account : *$charge->va_number*\n\n"
                . "Terima kasih atas perhatian dan kerjasamanya. \n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $client->messages->create('whatsapp:' . $noHp, [
                'from' => $whatsappFrom,
                'body' => $body,
            ]);
        }
        else {
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),  \n\n"
                . "Kami informasikan terdapat tagihan pembayaran *$categoryname* sebesar *Rp " . number_format($grossAmount, 0, ',', '.') . "*.\n\n"
                . "Silakan cek aplikasi atau hubungi pihak sekolah untuk info lebih lanjut.\n\n"
                . "Terima kasih atas perhatian dan kerjasamanya. \n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

            $client->messages->create('whatsapp:' . $noHp, [
                'from' => $whatsappFrom,
                'body' => $body,
            ]);
        }
    }

}
