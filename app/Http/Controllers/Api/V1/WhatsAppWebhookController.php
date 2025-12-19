<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\WhatsappMetaService;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook token from Meta
     * GET /api/v1/webhook/whatsapp
     */
    public function verify(Request $request)
    {
        try {
            $mode = $request->get('hub_mode');
            $token = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            Log::channel('whatsapp')->info('🔍 Webhook verify request received', [
                'mode' => $mode,
                'token_received' => $token ? '***' . substr($token, -10) : null,
                'challenge' => $challenge ? substr($challenge, 0, 20) . '...' : null,
            ]);

            $verifyToken = config('services.whatsapp.webhook_verify_token');

            if (!$verifyToken) {
                Log::channel('whatsapp')->error('❌ WHATSAPP_WEBHOOK_VERIFY_TOKEN not configured in .env');
                return response('Verify token not configured', 500);
            }

            // Verifikasi
            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::channel('whatsapp')->info('✅ Webhook verified successfully');
                return response($challenge, 200);
            }

            Log::channel('whatsapp')->error('❌ Webhook verification failed', [
                'expected_mode' => 'subscribe',
                'received_mode' => $mode,
                'token_match' => $token === $verifyToken,
            ]);

            return response('Unauthorized', 403);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Webhook verify exception', [
                'error' => $e->getMessage(),
            ]);
            return response('Error', 500);
        }
    }

    /**
     * Handle incoming messages from Meta
     * POST /api/v1/webhook/whatsapp
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->json()->all();

            Log::channel('whatsapp')->info('📨 Webhook POST received', [
                'object' => $data['object'] ?? null,
                'has_entry' => isset($data['entry']) && is_array($data['entry']),
                'has_field' => isset($data['field']),
            ]);

            // Handle format baru (field/value)
            if (isset($data['field'])) {
                $field = $data['field'];
                $value = $data['value'] ?? [];

                if ($field === 'messages') {
                    Log::channel('whatsapp')->info('✉️ Processing messages webhook');
                    $this->processMessages($value);
                } elseif ($field === 'account_alerts') {
                    Log::channel('whatsapp')->info('🔔 Processing account alerts webhook');
                    $this->processAccountAlerts($value);
                }
            }
            // Handle format lama (object/entry)
            elseif ($data['object'] === 'whatsapp_business_account' && isset($data['entry'])) {
                Log::channel('whatsapp')->info('📦 Processing legacy format webhook');
                foreach ($data['entry'] as $entry) {
                    $this->processEntry($entry);
                }
            }
            // Unknown format
            else {
                Log::channel('whatsapp')->warning('⚠️ Unknown webhook format', [
                    'keys' => array_keys($data),
                ]);
            }

            return response('ok', 200);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Webhook handle error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return response('ok', 200);
        }
    }

    /**
     * Process entry (legacy format support)
     */
    private function processEntry($entry)
    {
        if (!isset($entry['changes']) || !is_array($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? [];

            Log::channel('whatsapp')->info('Processing change field', [
                'field' => $field,
            ]);

            if ($field === 'messages') {
                $this->processMessages($value);
            } elseif ($field === 'account_alerts') {
                $this->processAccountAlerts($value);
            } else {
                // Ignore other fields (user_preferences, etc)
                Log::channel('whatsapp')->debug('Ignoring field', ['field' => $field]);
            }
        }
    }

    /**
     * Process messages from webhook
     */
    private function processMessages($value)
    {
        // Handle incoming messages
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleMessage($message, $value);
            }
        }

        // Handle message statuses
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleMessageStatus($status);
            }
        }
    }

    /**
     * Process account alerts
     */
    private function processAccountAlerts($value)
    {
        $alertType = $value['alert_type'] ?? null;
        $alertSeverity = $value['alert_severity'] ?? null;

        Log::channel('whatsapp')->info('🚨 Account alert received', [
            'alertType' => $alertType,
            'alertSeverity' => $alertSeverity,
        ]);

        switch ($alertType) {
            case 'OBA_APPROVED':
                Log::channel('whatsapp')->info('✅ OBA approved - WhatsApp Business Account verified');
                break;

            case 'OBA_REJECTED':
                Log::channel('whatsapp')->error('❌ OBA rejected - need to check requirements');
                break;

            case 'PHONE_NUMBER_QUALITY_ISSUE':
                Log::channel('whatsapp')->warning('⚠️ Phone number quality issue detected');
                break;

            case 'PHONE_NUMBER_FLAGGED':
                Log::channel('whatsapp')->error('❌ Phone number flagged');
                break;
        }
    }

    /**
     * Handle incoming message
     */
    private function handleMessage($message, $value)
    {
        try {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? now()->timestamp;
            $type = $message['type'] ?? 'text';

            // Validasi data penting
            if (!$from || !$messageId) {
                Log::channel('whatsapp')->warning('⚠️ Message missing from or messageId', [
                    'from' => $from,
                    'messageId' => $messageId,
                ]);
                return;
            }

            Log::channel('whatsapp')->info('📬 Message received', [
                'from' => $from,
                'type' => $type,
                'messageId' => $messageId,
            ]);

            // Ambil contact info
            $profileName = null;
            if (isset($value['contacts']) && is_array($value['contacts']) && count($value['contacts']) > 0) {
                $profileName = $value['contacts'][0]['profile']['name'] ?? null;
            }

            // Store message in database dengan error handling
            try {
                DB::table('whatsapp_incoming_messages')->insert([
                    'message_id' => $messageId,
                    'phone' => $from,
                    'type' => $type,
                    'content' => json_encode($message),
                    'profile_name' => $profileName ?? 'Unknown',
                    'status' => 'received',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::channel('whatsapp')->error('❌ Failed to insert message', [
                    'error' => $e->getMessage(),
                    'messageId' => $messageId,
                ]);
                return;
            }

            // Handle different message types
            switch ($type) {
                case 'text':
                    $this->handleTextMessage($message, $from);
                    break;

                case 'image':
                    $this->handleImageMessage($message, $from);
                    break;

                case 'document':
                    $this->handleDocumentMessage($message, $from);
                    break;

                case 'button':
                    $this->handleButtonMessage($message, $from);
                    break;

                case 'interactive':
                    $this->handleInteractiveMessage($message, $from);
                    break;

                default:
                    Log::channel('whatsapp')->warning('⚠️ Unknown message type', [
                        'type' => $type,
                    ]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Handle message error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle text message
     */
    private function handleTextMessage($message, $from)
    {
        $text = $message['text']['body'] ?? '';

        Log::channel('whatsapp')->info('💬 Text message received', [
            'from' => $from,
            'text' => substr($text, 0, 100),
        ]);

        // Auto-reply
        // $this->sendAutoReply($from);

        // Process command if needed
        if (strtoupper(trim($text)) === 'STOP') {
            $this->handleOptOut($from);
        }
    }

    /**
     * Handle image message
     */
    private function handleImageMessage($message, $from)
    {
        $imageData = $message['image'] ?? [];
        $mediaId = $imageData['id'] ?? null;
        $mimeType = $imageData['mime_type'] ?? null;

        Log::channel('whatsapp')->info('🖼️ Image message received', [
            'from' => $from,
            'mediaId' => $mediaId,
            'mimeType' => $mimeType,
        ]);
    }

    /**
     * Handle document message
     */
    private function handleDocumentMessage($message, $from)
    {
        $documentData = $message['document'] ?? [];
        $mediaId = $documentData['id'] ?? null;
        $filename = $documentData['filename'] ?? null;

        Log::channel('whatsapp')->info('📄 Document message received', [
            'from' => $from,
            'mediaId' => $mediaId,
            'filename' => $filename,
        ]);
    }

    /**
     * Handle button message
     */
    private function handleButtonMessage($message, $from)
    {
        $buttonData = $message['button'] ?? [];
        $buttonText = $buttonData['text'] ?? '';

        Log::channel('whatsapp')->info('🔘 Button message received', [
            'from' => $from,
            'buttonText' => $buttonText,
        ]);
    }

    /**
     * Handle interactive message (list, buttons)
     */
    private function handleInteractiveMessage($message, $from)
    {
        $interactive = $message['interactive'] ?? [];
        $type = $interactive['type'] ?? null;

        Log::channel('whatsapp')->info('📋 Interactive message received', [
            'from' => $from,
            'type' => $type,
        ]);

        if ($type === 'button_reply') {
            $buttonId = $interactive['button_reply']['id'] ?? null;
            $buttonTitle = $interactive['button_reply']['title'] ?? null;

            Log::channel('whatsapp')->info('✓ Button clicked', [
                'from' => $from,
                'buttonId' => $buttonId,
                'buttonTitle' => $buttonTitle,
            ]);
        }

        if ($type === 'list_reply') {
            $itemId = $interactive['list_reply']['id'] ?? null;
            $itemTitle = $interactive['list_reply']['title'] ?? null;

            Log::channel('whatsapp')->info('✓ List item selected', [
                'from' => $from,
                'itemId' => $itemId,
                'itemTitle' => $itemTitle,
            ]);
        }
    }

    /**
     * Handle message status (delivery, read, failed)
     */
    private function handleMessageStatus($status)
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusType = $status['status'] ?? null;

            if (!$messageId) {
                Log::channel('whatsapp')->warning('⚠️ Status update missing messageId');
                return;
            }

            Log::channel('whatsapp')->info('📊 Message status update', [
                'messageId' => $messageId,
                'status' => $statusType,
            ]);

            // Save to database
            DB::table('whatsapp_message_statuses')->insert([
                'message_id' => $messageId,
                'status' => $statusType,
                'recipient' => $status['recipient_id'] ?? null,
                'raw' => json_encode($status),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log status changes
            match ($statusType) {
                'sent' => Log::channel('whatsapp')->info('✉️ Message sent', ['messageId' => $messageId]),
                'delivered' => Log::channel('whatsapp')->info('📦 Message delivered', ['messageId' => $messageId]),
                'read' => Log::channel('whatsapp')->info('👁️ Message read', ['messageId' => $messageId]),
                'failed' => Log::channel('whatsapp')->error('❌ Message failed', [
                    'messageId' => $messageId,
                    'error' => $status['errors'] ?? [],
                ]),
                default => null
            };

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Handle status error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send auto-reply message
     */
    private function sendAutoReply($phone)
    {
        try {
            $whatsapp = new WhatsappMetaService();

            $message = "Terima kasih sudah menghubungi SD Muhammadiyah.\n\n"
                . "Kami akan merespon pertanyaan Anda dalam waktu 2 jam kerja.\n\n"
                . "Reply STOP untuk berhenti menerima pesan.";

            $whatsapp->sendMessage($phone, $message);

            Log::channel('whatsapp')->info('🤖 Auto-reply sent', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Auto-reply error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle opt-out (user wants to stop receiving messages)
     */
    private function handleOptOut($phone)
    {
        try {
            DB::table('whatsapp_consents')
                ->updateOrInsert(
                    ['phone' => $phone],
                    [
                        'opted_in' => false,
                        'opted_out_at' => now(),
                        'reason' => 'User replied STOP',
                        'updated_at' => now(),
                    ]
                );

            Log::channel('whatsapp')->info('🚫 User opted out', ['phone' => $phone]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Opt-out error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Debug configuration
     * GET /api/v1/whatsapp/config
     */
    public function debugConfig()
    {
        return response()->json([
            'api_url' => config('services.whatsapp.api_url'),
            'phone_id' => config('services.whatsapp.phone_id'),
            'business_id' => config('services.whatsapp.business_id'),
            'has_token' => !empty(config('services.whatsapp.access_token')),
        ]);
    }

    /**
     * Test sending message
     * POST /api/v1/whatsapp/test
     */
    public function test()
    {
        $whatsApp = new WhatsappMetaService();
        $result = $whatsApp->sendTemplate(
            '082217160075',
            'spp_reminder',
            [
                'User Test',
                'Kelas 2',
                'Januari',
                '20.000'
            ],
            "https://ansor.sdmuhammadiyah3smd.com//storage/1/qr_code_1.png"
        );

        // Return response lengkap untuk debugging
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function verifyAcc(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);
    }

    /**
     * Get templates
     * GET /api/v1/whatsapp/template
     */
    public function getTemplate()
    {
        $whatsapp = new WhatsappMetaService();
        $templates = $whatsapp->getTemplates();
        return response()->json($templates);
    }
}
