<?php

namespace App\Http\Controllers\Api\Dashboard;

use Twilio\Rest\Client as TwilioClient;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendOrderIDWhatsAppApi extends Controller
{
    public function sendMessage($orderId)
    {
        // Retrieve the payment information
        // $pembayaran = Pembayaran::with(['siswa', 'kelas'])->where('order_id', $orderId)->first();
        $pembayaran = Pembayaran::with(['siswa', 'kelas'])->where('order_id', $orderId)->first();
        if (!$pembayaran) {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }

        // Prepare recipient details
        // $no_hp = '+62' . ltrim($pembayaran->siswa->no_hp, '0'); // Format phone number for WhatsApp
        $no_hp = '+6285349734475';
        $siswa_name = $pembayaran->siswa->name;
        $siswa_kelas = $pembayaran->kelas->name;
        $gross_amount = intval($pembayaran->gross_amount);

        // Retrieve Twilio credentials from environment variables
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

        if (!$sid || !$token || !$whatsappFrom) {
            return response()->json(['status' => 'error', 'message' => 'Twilio configuration is missing.'], 500);
        }

        try {
            // Initialize the Twilio client
            $client = new TwilioClient($sid, $token);

            $message = $client->messages->create(
                'whatsapp:+6282217160075',
                [
                    // 'from' => $whatsappFrom,
                    "from" => "whatsapp:+14155238886",
                    'body' => "Halo Bapak/Ibu Dari $siswa_name, \n\n $siswa_kelas Memiliki Pembayaran with Order ID: $orderId has been received. The amount is Rp " . number_format($gross_amount, 0, ',', '.') . ". Thank you!"
                ]
            );

            return response()->json(['status' => 'success', 'message_sid' => $message->sid], 200);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
