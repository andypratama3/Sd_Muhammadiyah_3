<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Models\Charge;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client as TwilioClient;

class SendOrderIDWhatsAppApi extends Controller
{
    public function sendMessage($orderId)
    {
        $charge = Charge::with(['siswa.kelas'])->where('order_id', $orderId)->first();
        if (! $charge) {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        $categoryname = $charge->kategori_pembayaran->name;


        $siswa = $charge->siswa->first();
        $kelas = $siswa->kelas->first();
        $grossAmount = intval($charge->gross_amount);
        $namaSiswa = $siswa->name;
        $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
        $noHp = '+62' . ltrim($siswa->no_hp ?? '85349734475', '0');


        // Twilio credentials
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

        if (! $sid || ! $token || ! $whatsappFrom) {
            return response()->json(['status' => 'error', 'message' => 'Konfigurasi Twilio tidak lengkap.'], 500);
        }

        try {
            $qrImageUrl = $charge->url_action;
            $categoryname = $charge->kategori_pembayaran->name;

            // Pesan WhatsApp
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  "
                    . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (Kelas *$kelasSiswa*),  "
                    . "Izin kami sampaikan bahwa terdapat tagihan pembayaran pendidikan dengan rincian sebagai berikut:  "
                    . "📌 *Kategori Pembayaran*: $categoryname "
                    . "💰 *Jumlah Tagihan*: Rp " . number_format($grossAmount, 0, ',', '.') . " Bulan :   "
                    . "Untuk kemudahan transaksi, silakan melakukan pembayaran dengan memindai QR Code berikut: "
                    . "$qrImageUrl  "
                    . "🕊️ Mohon melakukan pembayaran tepat waktu demi kelancaran proses belajar-mengajar.  "
                    . "Apabila telah melakukan pembayaran, Ayah/Bunda tidak perlu membalas pesan ini.  "
                    . "Terima kasih atas perhatian dan kerjasamanya.  "
                    . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.";


            // Kirim pesan dengan gambar
            $client = new TwilioClient($sid, $token);
            $message = $client->messages->create(
                'whatsapp:' . $noHp,
                [
                    'from' => $whatsappFrom,
                    'body' => $body,
                    "mediaUrl" => ["https://images.unsplash.com/photo-1431250620804-78b175d2fada?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1600&h=900&fit=crop&ixid=eyJhcHBfaWQiOjF9"],

                ]
            );

            return response()->json(['status' => 'success', 'message_sid' => $message->sid], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {

    }
}
