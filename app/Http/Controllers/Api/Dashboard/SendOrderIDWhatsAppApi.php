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

        // Access token for the WhatsApp Business API
        $accessToken = env('WA_Busines');

        $client = new Client();
        $response = $client->post("https://graph.facebook.com/v20.0/401364996388545/messages", [
            'headers' => [
                'Authorization' => "Bearer EAAQGovTCT1cBO6R9kKVs4JFxiCXDuPuhyqdFtmB6OuqcyhNCTw9EZA2JkQuapkWzZCIpU9vinyRusIZBbDLVZBnH4tIO3kLsUVYDDC1BYtIlfZBZCNTa7K52Ia8O15vCIYFZBNsLE080tL6gXPlwxnKUBWCT1NZCK9GBt718OJnHcpHxVZCNWZBpMQg85UrmvtOKxezQZDZD",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => "$no_hp",
                'type' => 'template',
                'template' => [
                    'name' => 'payment_sd',
                    'language' => [
                        'code' => 'en_US'
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $siswa_name // Replaces {{1}}
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $siswa_kelas // Replaces {{2}}
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $orderId // Replaces {{3}}
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "Rp " . number_format($gross_amount, 0, ',', '.') // Replaces {{4}}
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ]);
        $responseData = $response->getBody()->getContents();

        return $responseData;
    }
}
