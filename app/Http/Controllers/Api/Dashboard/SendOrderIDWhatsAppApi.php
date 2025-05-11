<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Models\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client as TwilioClient;

class SendOrderIDWhatsAppApi extends Controller
{
    // public function sendMessage($orderId)
    // {
    //     $charge = Charge::with(['siswa'])->where('order_id', $orderId)->first();



    //     if (!$charge) {
    //         return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
    //     }

    //     // Prepare recipient details
    //     // $no_hp = '+62' . ltrim($pembayaran->siswa->no_hp, '0'); // Format phone number for WhatsApp
    //     $no_hp = '+6285349734475';
    //     $siswa_name = $charge->siswa->first()->name;
    //     $siswa_kelas = $charge->siswa->kelas->first()->name;
    //     $gross_amount = intval($charge->siswa->first()->spp);

    //     // Retrieve Twilio credentials from environment variables
    //     // $sid = env('TWILIO_SID');
    //     // $token = env('TWILIO_AUTH_TOKEN');
    //     // $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

    //     // if (!$sid || !$token || !$whatsappFrom) {
    //     //     return response()->json(['status' => 'error', 'message' => 'Twilio configuration is missing.'], 500);
    //     // }

    //     // try {
    //     //     // Initialize the Twilio client
    //     //     $client = new TwilioClient($sid, $token);

    //     //     $message = $client->messages->create(
    //     //         'whatsapp:+6282217160075',
    //     //         [
    //     //             // 'from' => $whatsappFrom,
    //     //             "from" => "whatsapp:+14155238886",
    //     //             'body' => "Halo Bapak/Ibu Dari $siswa_name, \n\n $siswa_kelas Memiliki Pembayaran with Order ID: $orderId has been received. The amount is Rp " . number_format($gross_amount, 0, ',', '.') . ". Thank you!"
    //     //         ]
    //     //     );

    //     //     dd($message);

    //     //     return response()->json(['status' => 'success', 'message_sid' => $message->sid], 200);

    //     // } catch (\Exception $e) {
    //     //     // Handle exceptions
    //     //     return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     // }


    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //     CURLOPT_URL => 'https://api.fonnte.com/send',
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     CURLOPT_POSTFIELDS => array(
    //     'target' => '08123456789,08987654321',
    //     'message' => 'test message',
    //     'delay' => '2',
    //     'countryCode' => '62', //optional
    //     ),
    //     CURLOPT_HTTPHEADER => array(
    //         'Authorization: TOKEN' //change TOKEN to your actual token
    //     ),
    //     ));

    //     $response = curl_exec($curl);

    //     curl_close($curl);
    //     echo $response;
    // }
     public function sendMessage($orderId)
    {
        // Ambil data pembayaran dan relasi siswa & kelas
        $charge = Charge::with(['siswa.kelas'])->where('order_id', $orderId)->first();

        if (!$charge || !$charge->siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan.'], 404);
        }

        $siswa = $charge->siswa->first(); // Jika relasinya hasMany
        if (!$siswa || !$siswa->kelas) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa atau kelas tidak lengkap.'], 422);
        }

        $namaSiswa = $siswa->name;
        $kelas = $siswa->kelas->first()->name;
        $noHp = $siswa->no_hp ?? '0';
        $nomorTujuan = '62' . ltrim($noHp, '0');
        $jumlahTagihan = number_format(intval($siswa->spp), 0, ',', '.');

        $pesan = " *Tagihan SPP*\n\n"
            . "SD Muhammadiyah 3\n"
            . "Kepada Yth. Bapak/Ibu dari *$namaSiswa*.\n"
            . "Siswa kelas *$kelas*.\n"
            . "NISN: *$siswa->nisn*\n"
            . "*Order ID:* $orderId\n"
            . "*Jumlah Tagihan:* Rp $jumlahTagihan\n\n"
            . "Silakan melakukan pembayaran ke Virtual Account berikut: *$charge->va_number*\n\n"
            . "Terima kasih atas perhatian dan kerja samanya 🙏";

        // Ganti semua karakter newline dengan whitespace

        // Kirim melalui Fonnte
        $response = Http::withHeaders([
            'Authorization' => env('FONNTE_TOKEN')
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $nomorTujuan,
            'message' => $pesan,
            'delay' => 5,
            'countryCode' => '62'
        ]);

        if ($response->successful()) {
            return response()->json(['status' => 'success', 'response' => $response->json()], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => $response->body()], 500);
        }
    }
}
