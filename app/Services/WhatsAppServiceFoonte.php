<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FONNTE WHATSAPP SERVICE
 *
 * Documentation: https://docs.fonnte.com/api-send-message/
 *
 * PENTING: Semua endpoint (text, image, file) menggunakan:
 * POST https://api.fonnte.com/send
 *
 * Yang berbeda adalah parameter:
 * - Text only: target + message
 * - Text + Image: target + message + url
 * - File: target + message + url + filename
 */

class WhatsAppServiceFoonte
{
    protected $apiUrl = 'https://api.fonnte.com/send';
    protected $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');

        if (!$this->token) {
            throw new \Exception('FONNTE_TOKEN tidak ditemukan di .env');
        }
    }

    /**
     * SEND TEXT MESSAGE ONLY
     *
     * Contoh:
     * $foonte = new WhatsAppServiceFoonte();
     * $result = $foonte->sendMessage('628217160075', 'Halo, ini pesan test');
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            $to = $this->formatPhone($to);

            Log::channel('whatsapp')->info('Fonnte sending text message', [
                'to' => $to,
                'message_length' => strlen($message)
            ]);

            $payload = [
                'target' => $to,
                'message' => $message,
                'countryCode' => '62'
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->token
                ])
                ->asForm()
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'send_message');

        } catch (\Exception $e) {
            Log::error('Fontte send message exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * SEND IMAGE WITH TEXT
     *
     * Gunakan endpoint yang SAMA (/send), tapi tambah parameter 'url'
     *
     * Contoh:
     * $result = $foonte->sendImage(
     *     '628217160075',
     *     'https://example.com/qr-code.png',
     *     'Silakan pindai QR Code ini untuk pembayaran'
     * );
     */
    public function sendImage(string $to, string $imageUrl, string $caption = null): array
    {
        try {
            $to = $this->formatPhone($to);

            // Validasi URL
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception("Invalid image URL: $imageUrl");
            }

            Log::info('Fonnte sending image', [
                'to' => $to,
                'image_url' => $imageUrl,
                'has_caption' => !is_null($caption)
            ]);

            // Build payload
            $payload = [
                'target' => $to,
                'url' => $imageUrl,  // PARAMETER PENTING UNTUK IMAGE!
                'countryCode' => '62'
            ];

            // Jika ada caption/message, tambahkan
            if ($caption) {
                $payload['message'] = $caption;
            }

            // ENDPOINT SAMA: /send (bukan /send-file)
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => $this->token
                ])
                ->asForm()
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'send_image');

        } catch (\Exception $e) {
            Log::error('Fontte send image exception', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * SEND FILE (PDF, DOCUMENT, AUDIO, VIDEO, etc)
     *
     * Sama seperti image, tapi dengan filename parameter
     *
     * Contoh:
     * $result = $foonte->sendFile(
     *     '628217160075',
     *     'https://example.com/invoice.pdf',
     *     'Berikut adalah invoice Anda',
     *     'invoice-2024.pdf'
     * );
     */
    public function sendFile(
        string $to,
        string $fileUrl,
        string $caption = null,
        string $filename = null
    ): array {
        try {
            $to = $this->formatPhone($to);

            if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception("Invalid file URL: $fileUrl");
            }

            Log::info('Fonnte sending file', [
                'to' => $to,
                'file_url' => $fileUrl,
                'filename' => $filename,
                'has_caption' => !is_null($caption)
            ]);

            $payload = [
                'target' => $to,
                'url' => $fileUrl,
                'countryCode' => '62'
            ];

            if ($caption) {
                $payload['message'] = $caption;
            }

            if ($filename) {
                $payload['filename'] = $filename;
            }

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => $this->token
                ])
                ->asForm()
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'send_file');

        } catch (\Exception $e) {
            Log::error('Fontte send file exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * SEND MESSAGE WITH TEXT + IMAGE (Convenience method)
     *
     * Alias untuk sendImage untuk kemudahan
     */
    public function sendMessageWithImage(
        string $to,
        string $message,
        string $imageUrl
    ): array {
        return $this->sendImage($to, $imageUrl, $message);
    }

    /**
     * VALIDATE PHONE NUMBER
     *
     * Cek apakah nomor terdaftar di WhatsApp
     */
    public function validateNumber(string $to): array
    {
        try {
            $to = $this->formatPhone($to);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->token
                ])
                ->post('https://api.fonnte.com/validate-number', [
                    'target' => $to,
                    'countryCode' => '62'
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * CHECK DEVICE STATUS
     *
     * Pastikan device Fonnte tetap connected
     */
    public function checkStatus(): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->token
                ])
                ->post('https://api.fonnte.com/get-profile');

            if ($response->failed()) {
                return [
                    'success' => false,
                    'status' => 'disconnected',
                    'error' => $response->body()
                ];
            }

            return [
                'success' => true,
                'status' => 'connected',
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * HANDLE RESPONSE - Internal method
     *
     * Standardize response handling dari Fonnte
     */
    private function handleResponse($response, string $action): array
    {
        // Debug
        Log::debug("Fonnte $action response", [
            'status_code' => $response->status(),
            'body' => $response->body()
        ]);

        // Check HTTP status
        if ($response->failed()) {
            $error = $response->json();
            Log::warning("Fonnte $action failed", [
                'status' => $response->status(),
                'error' => $error
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? $response->body(),
                'http_status' => $response->status(),
                'data' => $error
            ];
        }

        // Check Fonnte API status
        $data = $response->json();
        $isSuccess = $data['status'] ?? false;

        if ($isSuccess) {
            Log::info("Fontte $action success", [
                'response' => $data
            ]);
        } else {
            Log::warning("Fontte $action api error", [
                'response' => $data
            ]);
        }

        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Success' : 'Failed',
            'data' => $data
        ];
    }

    /**
     * FORMAT PHONE NUMBER - Internal method
     *
     * Convert dari berbagai format ke format Fonnte (62...)
     */
    private function formatPhone(string $phone): string
    {
        // Remove semua non-digit
        $cleaned = preg_replace('/\D/', '', $phone);

        // Validasi panjang
        if (strlen($cleaned) < 10) {
            throw new \Exception("Phone number terlalu pendek: $phone");
        }

        // Jika sudah dimulai dengan 62
        if (strpos($cleaned, '62') === 0) {
            return $cleaned;
        }

        // Jika dimulai dengan 0, ganti dengan 62
        if (strpos($cleaned, '0') === 0) {
            return '62' . substr($cleaned, 1);
        }

        // Jika dimulai dengan 8 (tanpa 0), tambahi 62
        if (strpos($cleaned, '8') === 0) {
            return '62' . $cleaned;
        }

        // Fallback: prefix dengan 62
        return '62' . $cleaned;
    }
}
