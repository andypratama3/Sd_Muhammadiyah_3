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
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://graph.facebook.com/v22.0'; // atau dari config yang benar
        $this->phoneId = config('services.whatsapp.phone_id');
        $this->bisnisId = config('services.whatsapp.business_id');
        $this->accessToken = config('services.whatsapp.access_token');

        // Debug - log config
        Log::channel('whatsapp')->info('WhatsApp Service Initialized', [
            'api_url' => $this->apiUrl,
            'phone_id' => $this->phoneId,
            'business_id' => $this->bisnisId,
            'has_token' => !empty($this->accessToken),
        ]);
    }

    /**
     * Send template message (recommended)
     */
    public function sendTemplate(string $phone, string $templateName, array $parameters = []): array
    {
        try {
            $phone = $this->formatPhone($phone);

            Log::channel('whatsapp')->info('Sending template', [
                'phone' => $phone,
                'template' => $templateName,
                'parameters' => $parameters,
            ]);

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

                    $body['template']['components'] = [
                        [
                            'type' => 'body',
                            'parameters' => $bodyParams
                        ]
                    ];
                }


            Log::channel('whatsapp')->debug('Request body', $body);

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", $body);

            $responseData = $response->json();

            Log::channel('whatsapp')->info('API Response', [
                'status' => $response->status(),
                'body' => $responseData,
            ]);

            if ($response->successful()) {
                Log::channel('whatsapp')->info('Template sent', [
                    'phone' => $phone,
                    'template' => $templateName,
                    'messageId' => $responseData['messages'][0]['id'] ?? null,
                ]);
                return [
                    'success' => true,
                    'message' => 'Template sent successfully',
                    'data' => $responseData,
                ];
            }

            // Status bukan 200 - ada error
            Log::channel('whatsapp')->error('Template failed', [
                'status' => $response->status(),
                'error' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => 'Template failed to send',
                'error' => $responseData,
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Template error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send text message (free text, limited)
     */
    public function sendMessage(string $phone, string $message, ?string $imageUrl = null): array
    {
        try {
            $phone = $this->formatPhone($phone);

            Log::channel('whatsapp')->info('Sending message', [
                'phone' => $phone,
                'message_length' => strlen($message),
                'has_image' => !is_null($imageUrl),
            ]);

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
            ];

            // Jika ada gambar, kirim sebagai image dengan caption
            if ($imageUrl) {
                $payload['type'] = 'image';
                $payload['image'] = [
                    'link' => $imageUrl,
                    'caption' => $message,
                ];
            } else {
                // Jika tidak ada gambar, kirim sebagai text biasa
                $payload['type'] = 'text';
                $payload['text'] = ['body' => $message];
            }

            $response = Http::timeout(30)
                ->withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::channel('whatsapp')->info('Message sent', [
                    'phone' => $phone,
                    'type' => $payload['type'],
                ]);
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $responseData,
                ];
            }

            Log::channel('whatsapp')->error('Message failed', [
                'status' => $response->status(),
                'error' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => 'Message failed to send',
                'error' => $responseData,
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Message error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to WhatsApp format (62...)
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

    /**
     * Get all templates from Meta
     * URL yang benar: https://graph.facebook.com/v22.0/{business_id}/message_templates
     */
    public function getTemplates(): array
    {
        try {
            // ✅ PENTING: Gunakan Business ID bukan Phone ID
            $url = "{$this->apiUrl}/{$this->bisnisId}/message_templates";

            Log::channel('whatsapp')->info('Fetching templates', [
                'url' => $url,
                'business_id' => $this->bisnisId,
            ]);

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $templates = json_decode($response->getBody()->getContents(), true);

            Log::channel('whatsapp')->info('Templates fetched', [
                'count' => count($templates['data'] ?? []),
            ]);

            return $templates;

        } catch (Exception $e) {
            Log::channel('whatsapp')->error('Get templates error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
