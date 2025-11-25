<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappMetaService
{
    protected $apiUrl;
    protected $phoneId;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->phoneId = config('services.whatsapp.phone_id');
        $this->bisnisId = config('services.whatsapp.business_id');
        $this->accessToken = config('services.whatsapp.access_token');
    }

    /**
     * Send template message (recommended)
     */
    public function sendTemplate(string $phone, string $templateName, array $parameters = []): bool
    {
        try {
            $phone = $this->formatPhone($phone);

            $body = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'id'],
                ],
            ];

            if (!empty($parameters)) {
                $bodyParams = array_map(
                    fn($param) => ['type' => 'text', 'text' => (string)$param],
                    $parameters
                );
                $body['template']['parameters'] = ['body' => ['parameters' => $bodyParams]];
            }

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", $body);

            if ($response->successful()) {
                $data = $response->json();
                Log::channel('whatsapp')->info('Template sent', [
                    'phone' => $phone,
                    'template' => $templateName,
                    'messageId' => $data['messages'][0]['id'] ?? null,
                ]);
                return true;
            }

            Log::channel('whatsapp')->error('Template failed', [
                'status' => $response->status(),
                'error' => $response->json(),
            ]);
            return false;

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Template error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send text message (free text, limited)
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $phone = $this->formatPhone($phone);

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            if ($response->successful()) {
                Log::channel('whatsapp')->info('Message sent', ['phone' => $phone]);
                return true;
            }

            Log::channel('whatsapp')->error('Message failed', [
                'error' => $response->json(),
            ]);
            return false;

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Message error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Format phone number
     */
    private function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (strpos($cleaned, '62') === 0) {
            return $cleaned;
        }

        if (strpos($cleaned, '0') === 0) {
            return '62' . substr($cleaned, 1);
        }

        return '62' . $cleaned;
    }

    public function debugSendTemplate($to, $templateName, $parameters = [])
    {
        try {
            $url = "$this->apiUrl/" . $this->phoneId . "/messages";

            $body = [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "type" => "template",
                "template" => [
                    "name" => $templateName,
                    "language" => ["code" => "id"],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $parameters['name']],
                                ["type" => "text", "text" => $parameters['kelasSiswa']],
                                ["type" => "text", "text" => $parameters['monthName']],
                                ["type" => "text", "text" => number_format($parameters['grossAmount'], 0, ',', '.')],
                            ]
                        ]
                    ]
                ]
            ];

            $response = $this->client->post($url, [
                "headers" => [
                    "Authorization" => "Bearer " . $this->accessToken,
                    "Content-Type" => "application/json"
                ],
                "json" => $body
            ]);

            // 🔥 return hasil response API
            return json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // 🚨 lempar error API ke controller
            throw $e;
        } catch (\Exception $e) {
            // 🚨 lempar error umum ke controller
            throw $e;
        }
    }

    // getTemplate Meta Messgae

    public function getTemplates()
    {
        try {
            $url = "$this->apiUrl/" . $this->bisnisId . "/message_templates";


            $response = $this->client->get($url, [
                "headers" => [
                    "Authorization" => "Bearer " . $this->accessToken,
                    "Content-Type" => "application/json"
                ]
            ]);

            // 🔥 return hasil response API
            return json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // 🚨 lempar error API ke controller
            throw $e;
        } catch (\Exception $e) {
            // 🚨 lempar error umum ke controller
            throw $e;
        }
    }

}
