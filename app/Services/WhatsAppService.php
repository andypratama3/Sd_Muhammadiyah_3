<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $version;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->version = config('services.whatsapp.version', 'v19.0');
    }

    public function sendMessage($to, $message, $mediaUrl = null)
    {
        $url = "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $mediaUrl ? 'image' : 'text',
        ];

        if ($mediaUrl) {
            $data['image'] = [
                'link' => $mediaUrl,
                'caption' => $message
            ];
        } else {
            $data['text'] = [
                'body' => $message
            ];
        }

        return Http::withToken($this->token)
            ->post($url, $data)
            ->json();
    }
}
