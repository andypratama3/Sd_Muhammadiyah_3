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
                        ->where('order_id', $this->orderId)->first();

        if (! $charge) return;

        $categoryname = $charge->kategori_pembayaran->name;
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $siswa = $charge->siswa;
        $kelas = $siswa->kelas->first();
        $grossAmount = intval($charge->gross_amount);
        $namaSiswa = $siswa->name;
        $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
        $noHp = '+62' . ltrim($siswa->no_hp ?? '85349734475', '0');

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

        $qrImageUrl = $charge->url_action;
        $fileName = 'qr-siswa-' . Str::slug($categoryname . '-' . $monthName . '-' . $namaSiswa, '-') . '.png';
        $relativePath = 'img/waqr/spp/';
        $storagePath = storage_path('app/public/' . $relativePath);

        ImageHelper::resizeAndSave($qrImageUrl, $storagePath, $fileName, 512, 512);

        $publicUrl = asset('storage/' . $relativePath . $fileName);

        $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
            . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),  \n\n"
            . "Izin kami sampaikan bahwa terdapat tagihan pembayaran pendidikan:\n\n"
            . "📌 *Kategori*: $categoryname \n"
            . "💰 *Jumlah*: Rp " . number_format($grossAmount, 0, ',', '.') . "\n"
            . "🗓️ Bulan: $monthName \n\n"
            . "Silakan pindai QR Code berikut untuk pembayaran:\n"
            . "$publicUrl\n\n"
            . "Terima kasih. \n"
            . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";

        $client = new TwilioClient($sid, $token);
        $client->messages->create('whatsapp:' . $noHp, [
            'from' => $whatsappFrom,
            'body' => $body,
            'mediaUrl' => [$publicUrl],
        ]);
    }
}
