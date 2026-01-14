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

            Log::channel('whatsapp')->info('Webhook verify request', [
                'mode' => $mode,
                'token_match' => $token === config('services.whatsapp.webhook_verify_token'),
            ]);

            $verifyToken = config('services.whatsapp.webhook_verify_token');

            if (!$verifyToken) {
                Log::channel('whatsapp')->error('❌ Verify token not configured');
                return response('Verify token not configured', 500);
            }

            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::channel('whatsapp')->info('✅ Webhook verified successfully');
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }

            Log::channel('whatsapp')->error('❌ Webhook verification failed');
            return response('Forbidden', 403);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Verify exception', [
                'error' => $e->getMessage(),
            ]);
            return response('Error', 500);
        }
    }

    /**
     * Handle incoming webhooks from Meta
     * POST /api/v1/webhook/whatsapp
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->json()->all();

            Log::channel('whatsapp')->info('📨 Webhook received', [
                'object' => $data['object'] ?? null,
            ]);

            // Validate webhook format
            if (!isset($data['object']) || $data['object'] !== 'whatsapp_business_account') {
                Log::channel('whatsapp')->warning('⚠️ Invalid webhook object');
                return response()->json(['status' => 'ignored'], 200);
            }

            if (!isset($data['entry']) || !is_array($data['entry'])) {
                Log::channel('whatsapp')->warning('⚠️ No entries in webhook');
                return response()->json(['status' => 'ok'], 200);
            }

            // Process all entries
            foreach ($data['entry'] as $entry) {
                $this->processEntry($entry);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Webhook handle error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Always return 200 to prevent Meta from retrying
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 200);
        }
    }

    /**
     * Process webhook entry
     */
    private function processEntry($entry)
    {
        if (!isset($entry['changes']) || !is_array($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? [];

            switch ($field) {
                case 'messages':
                    $this->processMessages($value);
                    break;

                case 'message_template_status_update':
                    $this->processTemplateStatusUpdate($value);
                    break;

                case 'account_alerts':
                    $this->processAccountAlerts($value);
                    break;

                case 'account_update':
                    $this->processAccountUpdate($value);
                    break;

                default:
                    Log::channel('whatsapp')->debug('Ignoring field', ['field' => $field]);
            }
        }
    }

    /**
     * Process messages (incoming messages and status updates)
     */
    private function processMessages($value)
    {
        // Handle incoming messages from users
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($message, $value);
            }
        }

        // Handle message status updates (sent, delivered, read, failed)
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleMessageStatus($status);
            }
        }
    }

    /**
     * Handle incoming message from user
     */
    private function handleIncomingMessage($message, $value)
    {
        try {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? now()->timestamp;
            $type = $message['type'] ?? 'unknown';

            if (!$from || !$messageId) {
                Log::channel('whatsapp')->warning('⚠️ Invalid message data');
                return;
            }

            // Get contact info
            $profileName = 'Unknown';
            if (isset($value['contacts'][0]['profile']['name'])) {
                $profileName = $value['contacts'][0]['profile']['name'];
            }

            Log::channel('whatsapp')->info('📬 Incoming message', [
                'from' => $from,
                'name' => $profileName,
                'type' => $type,
                'messageId' => $messageId,
            ]);

            // Store in database
            $this->storeIncomingMessage([
                'message_id' => $messageId,
                'phone' => $from,
                'profile_name' => $profileName,
                'type' => $type,
                'content' => json_encode($message),
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
            ]);

            // UPDATE STATUS HP SISWA KETIKA ADA BALASAN PESAN
            $this->updateSiswaPhoneStatus($from);

            // Handle different message types
            match ($type) {
                'text' => $this->handleTextMessage($message, $from, $profileName),
                'image' => $this->handleMediaMessage($message, $from, 'image'),
                'document' => $this->handleMediaMessage($message, $from, 'document'),
                'audio' => $this->handleMediaMessage($message, $from, 'audio'),
                'video' => $this->handleMediaMessage($message, $from, 'video'),
                'button' => $this->handleButtonMessage($message, $from),
                'interactive' => $this->handleInteractiveMessage($message, $from),
                default => Log::channel('whatsapp')->info("Unhandled type: {$type}")
            };

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Handle incoming message error', [
                'error' => $e->getMessage(),
                'messageId' => $messageId ?? null,
            ]);
        }
    }

    /**
     * Update status HP siswa ketika ada balasan pesan
     * Mencari nomor HP di database siswa dan update status_hp menjadi 1 (true)
     */
    private function updateSiswaPhoneStatus($phoneNumber)
    {
        try {
            // Format nomor HP: hapus karakter khusus, pastikan hanya angka
            $formattedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Jika dimulai dengan 62, ubah menjadi 0
            if (strpos($formattedPhone, '62') === 0) {
                $formattedPhone = '0' . substr($formattedPhone, 2);
            }

            Log::channel('whatsapp')->info('🔍 Searching siswa phone', [
                'original' => $phoneNumber,
                'formatted' => $formattedPhone,
            ]);

            // Update siswa dengan nomor HP yang sesuai
            $updated = DB::table('siswas')
                ->where('no_hp', $formattedPhone)
                ->update([
                    'status_hp' => 1,
                    'status_hp_verified_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($updated > 0) {
                Log::channel('whatsapp')->info('✅ Siswa phone status updated', [
                    'phone' => $formattedPhone,
                    'updated_rows' => $updated,
                ]);
            } else {
                Log::channel('whatsapp')->warning('⚠️ Siswa not found with phone', [
                    'phone' => $formattedPhone,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Update siswa phone status error', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
        }
    }

    /**
     * Handle text message
     */
    private function handleTextMessage($message, $from, $profileName)
    {
        $text = $message['text']['body'] ?? '';

        Log::channel('whatsapp')->info('💬 Text message', [
            'from' => $from,
            'name' => $profileName,
            'text' => mb_substr($text, 0, 100),
        ]);

        // // Check for opt-out
        // $upperText = strtoupper(trim($text));
        // if (in_array($upperText, ['STOP', 'BERHENTI', 'UNSUBSCRIBE'])) {
        //     $this->handleOptOut($from);
        //     return;
        // }

        // // Check for opt-in
        // if (in_array($upperText, ['START', 'MULAI', 'SUBSCRIBE'])) {
        //     $this->handleOptIn($from);
        //     return;
        // }

        // Auto-reply (optional - uncomment if needed)
        // $this->sendAutoReply($from, $profileName);
    }

    /**
     * Handle media message (image, document, audio, video)
     */
    private function handleMediaMessage($message, $from, $type)
    {
        $mediaData = $message[$type] ?? [];
        $mediaId = $mediaData['id'] ?? null;
        $mimeType = $mediaData['mime_type'] ?? null;
        $filename = $mediaData['filename'] ?? null;

        Log::channel('whatsapp')->info("📎 Media message ({$type})", [
            'from' => $from,
            'mediaId' => $mediaId,
            'mimeType' => $mimeType,
            'filename' => $filename,
        ]);

        // TODO: Download and store media if needed
        // $this->downloadMedia($mediaId);
    }

    /**
     * Handle button message
     */
    private function handleButtonMessage($message, $from)
    {
        $buttonData = $message['button'] ?? [];
        $buttonText = $buttonData['text'] ?? '';
        $buttonPayload = $buttonData['payload'] ?? '';

        Log::channel('whatsapp')->info('🔘 Button clicked', [
            'from' => $from,
            'text' => $buttonText,
            'payload' => $buttonPayload,
        ]);

        // Handle button actions based on payload
        // Example: if ($buttonPayload === 'confirm_payment') { ... }
    }

    /**
     * Handle interactive message (list/button reply)
     */
    private function handleInteractiveMessage($message, $from)
    {
        $interactive = $message['interactive'] ?? [];
        $type = $interactive['type'] ?? null;

        if ($type === 'button_reply') {
            $buttonId = $interactive['button_reply']['id'] ?? null;
            $buttonTitle = $interactive['button_reply']['title'] ?? null;

            Log::channel('whatsapp')->info('✓ Button reply', [
                'from' => $from,
                'id' => $buttonId,
                'title' => $buttonTitle,
            ]);
        }

        if ($type === 'list_reply') {
            $itemId = $interactive['list_reply']['id'] ?? null;
            $itemTitle = $interactive['list_reply']['title'] ?? null;

            Log::channel('whatsapp')->info('✓ List reply', [
                'from' => $from,
                'id' => $itemId,
                'title' => $itemTitle,
            ]);
        }
    }

    /**
     * Handle message status update
     */
    private function handleMessageStatus($status)
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusType = $status['status'] ?? null;
            $recipientId = $status['recipient_id'] ?? null;
            $timestamp = $status['timestamp'] ?? now()->timestamp;

            if (!$messageId || !$statusType) {
                return;
            }

            Log::channel('whatsapp')->info('📊 Status update', [
                'messageId' => substr($messageId, -10),
                'status' => $statusType,
                'recipient' => $recipientId,
            ]);

            // Store in database
            $this->storeMessageStatus([
                'message_id' => $messageId,
                'status' => $statusType,
                'recipient' => $recipientId,
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
                'errors' => isset($status['errors']) ? json_encode($status['errors']) : null,
            ]);

            // Log specific statuses
            match ($statusType) {
                'sent' => Log::channel('whatsapp')->info('✉️ Sent'),
                'delivered' => Log::channel('whatsapp')->info('📦 Delivered'),
                'read' => Log::channel('whatsapp')->info('👁️ Read'),
                'failed' => Log::channel('whatsapp')->error('❌ Failed', [
                    'errors' => $status['errors'] ?? [],
                ]),
                default => null
            };

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Status update error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process template status update
     */
    private function processTemplateStatusUpdate($value)
    {
        $event = $value['event'] ?? null;
        $messageName = $value['message_template_name'] ?? null;
        $messageLanguage = $value['message_template_language'] ?? null;

        Log::channel('whatsapp')->info('📋 Template status update', [
            'event' => $event,
            'template' => $messageName,
            'language' => $messageLanguage,
        ]);

        // Events: APPROVED, REJECTED, PAUSED, DISABLED
    }

    /**
     * Process account alerts
     */
    private function processAccountAlerts($value)
    {
        $alertType = $value['alert_type'] ?? null;

        Log::channel('whatsapp')->warning('🚨 Account alert', [
            'type' => $alertType,
        ]);

        // Handle critical alerts
        if (in_array($alertType, ['PHONE_NUMBER_FLAGGED', 'PHONE_NUMBER_RESTRICTED'])) {
            // TODO: Send email notification to admin
            Log::channel('whatsapp')->error('🚨 CRITICAL ALERT', [
                'type' => $alertType,
                'details' => $value,
            ]);
        }
    }

    /**
     * Process account update
     */
    private function processAccountUpdate($value)
    {
        Log::channel('whatsapp')->info('🔄 Account update', $value);
    }

    /**
     * Store incoming message in database
     */
    private function storeIncomingMessage(array $data)
    {
        try {
            DB::table('whatsapp_incoming_messages')->insert([
                'message_id' => $data['message_id'],
                'phone' => $data['phone'],
                'profile_name' => $data['profile_name'],
                'type' => $data['type'],
                'content' => $data['content'],
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store message error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store message status in database
     */
    private function storeMessageStatus(array $data)
    {
        try {
            DB::table('whatsapp_message_statuses')->insert([
                'message_id' => $data['message_id'],
                'status' => $data['status'],
                'recipient' => $data['recipient'],
                'timestamp' => $data['timestamp'],
                'errors' => $data['errors'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store status error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send auto-reply
     */
    private function sendAutoReply($phone, $name)
    {
        try {
            $whatsapp = new WhatsappMetaService();

            $message = "Halo {$name}! 👋\n\n"
                . "Terima kasih telah menghubungi SD Muhammadiyah 3 Samarinda.\n\n"
                . "Tim kami akan segera merespons pesan Anda.\n\n"
                . "Balas STOP jika tidak ingin menerima pesan lagi.";

            $result = $whatsapp->sendMessage($phone, $message);

            if ($result['success']) {
                Log::channel('whatsapp')->info('🤖 Auto-reply sent', ['phone' => $phone]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Auto-reply error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle user opt-out
     */
    // private function handleOptOut($phone)
    // {
    //     try {
    //         DB::table('whatsapp_opt_outs')->updateOrInsert(
    //             ['phone' => $phone],
    //             [
    //                 'opted_out' => true,
    //                 'opted_out_at' => now(),
    //                 'updated_at' => now(),
    //             ]
    //         );

    //         Log::channel('whatsapp')->info('🚫 User opted out', ['phone' => $phone]);

    //         // // Send confirmation (optional)
    //         // $whatsapp = new WhatsappMetaService();
    //         // $whatsapp->sendMessage(
    //         //     $phone,
    //         //     "Anda telah berhenti menerima pesan dari SD Muhammadiyah 3 Samarinda.\n\nBalas START untuk berlangganan kembali."
    //         // );

    //     } catch (\Exception $e) {
    //         Log::channel('whatsapp')->error('❌ Opt-out error', [
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }

    /**
     * Handle user opt-in
     */
    // private function handleOptIn($phone)
    // {
    //     try {
    //         DB::table('whatsapp_opt_outs')->updateOrInsert(
    //             ['phone' => $phone],
    //             [
    //                 'opted_out' => false,
    //                 'opted_in_at' => now(),
    //                 'updated_at' => now(),
    //             ]
    //         );

    //         Log::channel('whatsapp')->info('✅ User opted in', ['phone' => $phone]);

    //         $whatsapp = new WhatsappMetaService();
    //         $whatsapp->sendMessage(
    //             $phone,
    //             "Terima kasih! Anda akan menerima update dari SD Muhammadiyah 3 Samarinda.\n\nBalas STOP untuk berhenti."
    //         );

    //     } catch (\Exception $e) {
    //         Log::channel('whatsapp')->error('❌ Opt-in error', [
    //             'error' => $e->getMessage(),
    //         ]);
    //     }
    // }

    /**
     * Test endpoint
     */
    public function test(Request $request)
    {
        $whatsApp = new WhatsappMetaService();

        $phone = $request->phone;

        $result = $whatsApp->sendTemplate(
            $phone,
            'general_payment_reminder',
            [
                'User Test',
                'Kelas 2',
                'Januari',
                '20.000'
            ]
        );

        // Return response lengkap untuk debugging
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get messages history
     */
    public function getMessagesHistory(Request $request)
    {
        $limit = $request->input('limit', 50);

        $whatsapp = new WhatsappMetaService();
        $result = $whatsapp->getMessagesHistory($limit);

        return response()->json($result);
    }

    /**
     * Get templates
     */
    public function getTemplate()
    {
        try {
            $whatsapp = new WhatsappMetaService();
            $templates = $whatsapp->getTemplates();
            return response()->json($templates);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
