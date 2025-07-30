<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppServiceFoonte
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        $this->apiUrl = 'https://api.fonnte.com/send';
        $this->token = env('FONNTE_TOKEN');
    }

    public function sendMessage(string $to, string $message, ?string $imageUrl = null): array
    {
        $payload = [
            'target' => $to,
            'message' => $message,
            'delay' => 2,
            'countryCode' => '62'
        ];

        if ($imageUrl) {
            $payload['url'] = $imageUrl;
        }

        $response = Http::withHeaders([
            'Authorization' => $this->token
        ])->asForm()->post($this->apiUrl, $payload);

        return $response->successful()
            ? ['success' => true, 'data' => $response->json()]
            : ['success' => false, 'error' => $response->body()];
    }
}
