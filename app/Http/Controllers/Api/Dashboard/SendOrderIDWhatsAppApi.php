<?php

namespace App\Http\Controllers\Api\Dashboard;

use GuzzleHttp\Client;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendOrderIDWhatsAppApi extends Controller
{
    public function sendMessage($orderId)
    {
        $pembayaran = Pembayaran::with(['siswa', 'kelas'])->where('order_id', $orderId)->first();
        $no_hp = $pembayaran->siswa->no_hp;
        $no_hp = '62' . substr($no_hp, 1);
        $no_hp = intval($no_hp);
        $siswa_name = $pembayaran->siswa->name;
        $siswa_kelas = $pembayaran->kelas->name;
        $gross_amount = intval($pembayaran->gross_amount);

        $accessToken = 'EAAQGovTCT1cBO34Tzwz1g3Ug16mLdml9IKNdgLgxZAkVbb2BcNV78a2ZBPGCGNwkcNZCSyR0whTvcZA150px7LRZBzvqmSka134JlhPqjrBlZADWh2dXh30cHIzw9hN0UZBbCsJD3gHvD8T653FbVsiCZCb70iZAAlqifx8OlTrLMOEppgWjyGCxqagb7igNauW3VyAG0ZCBhIffLMmQs4JtQSvZAFXdfcZD';

        $client = new Client();
        $response = $client->post("https://graph.facebook.com/v19.0/621196921592550/messages", [
            'headers' => [
                'Authorization' => "{$accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "messaging_product" => "whatsapp",
                "to" => $no_hp,
                "recipient_type" => "individual",
                "type" => "text",
                "text" => [
                    "preview_url" => false,
                    "body" => "Assalamualaikum wr.wb, Di beritahukan Kepada Orang Tua Siswa Bahwa Siswa $siswa_name dari kelas $siswa_kelas Memiliki Tagihan Baru Sebesar $gross_amount Silahkan Melakukan Pembayaran Di https://sd.relawanmhf.com/pembayaran"
                ],
                "language" => [
                    "code" => "en_US"
                ]
            ]
        ]);

        $responseData = $response->getBody()->getContents();

        return $responseData;
    }
}
