<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;

class WhatsappService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->from = env('TWILIO_WHATSAPP_FROM');
        $this->client = new TwilioClient($sid, $token);
    }

    /**
     * Kirim pesan WhatsApp dengan opsi media
     *
     * @param string $to Nomor tujuan format internasional (+62...)
     * @param string $body Pesan teks
     * @param array $mediaUrls Array URL media (opsional)
     * @return void
     */
    public function sendMessage(string $to, string $body, array $mediaUrls = [])
    {
        $params = [
            'from' => $this->from,
            'body' => $body,
        ];

        if (!empty($mediaUrls)) {
            $params['mediaUrl'] = $mediaUrls;
        }

        $this->client->messages->create('whatsapp:' . $to, $params);
    }
}
