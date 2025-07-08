<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Models\Charge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client as TwilioClient;

class SendOrderIDWhatsAppApi extends Controller
{
    // public function sendMessage($orderId)
    // {
    //     $charge = Charge::with(['siswa.kelas'])->where('order_id', $orderId)->first();
    //     if (! $charge) {
    //         return response()->json(['status' => 'error', 'message' => 'Pembayaran tidak ditemukan.'], 404);
    //     }

    //     $categoryname = $charge->kategori_pembayaran->name;

    //     $monthName = Carbon::now()->locale('id')->translatedFormat('F');


    //     $siswa = $charge->siswa->first();
    //     $kelas = $siswa->kelas->first();
    //     $grossAmount = intval($charge->gross_amount);
    //     $namaSiswa = $siswa->name;
    //     $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
    //     $noHp = '+62' . ltrim($siswa->no_hp ?? '85349734475', '0');


    //     // Twilio credentials
    //     $sid = env('TWILIO_SID');
    //     $token = env('TWILIO_AUTH_TOKEN');
    //     $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

    //     if (! $sid || ! $token || ! $whatsappFrom) {
    //         return response()->json(['status' => 'error', 'message' => 'Konfigurasi Twilio tidak lengkap.'], 500);
    //     }

    //     try {
    //         $qrImageUrl = $charge->url_action;
    //         $categoryname = $charge->kategori_pembayaran->name;
    //         // Pesan WhatsApp
    //         $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
    //                 . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (Kelas *$kelasSiswa*),  \n\n"
    //                 . "Izin kami sampaikan bahwa terdapat tagihan pembayaran pendidikan dengan rincian sebagai berikut: \n\n"
    //                 . "📌 *Kategori Pembayaran*: $categoryname \n"
    //                 . "💰 *Jumlah Tagihan*: Rp " . number_format($grossAmount, 0, ',', '.') . "\n"
    //                 . " Bulan : $monthName \n"
    //                 . "Untuk kemudahan transaksi, silakan melakukan pembayaran dengan memindai QR Code berikut: \n"
    //                 . "$qrImageUrl \n\n"
    //                 . "🕊️ Mohon melakukan pembayaran tepat waktu demi kelancaran proses belajar-mengajar.  \n"
    //                 . "Apabila telah melakukan pembayaran, Ayah/Bunda tidak perlu membalas pesan ini.\n\n"
    //                 . "Terima kasih atas perhatian dan kerjasamanya.  \n"
    //                 . "Wassalamu'alaikum Warahmatullahi Wabarakatuh. \n";

    //         // save the url to jpg or png  from midtrans qr code


    //         $contents = file_get_contents($qrImageUrl);

    //         $qrImageUrl = return response($contents)
    //             ->header('Content-Type', 'image/png')
    //             ->header('Content-Disposition', 'attachment; filename="qr-siswa-'.$charge->name. $monthName .'.png"');


    //         // Kirim pesan dengan gambar
    //         $client = new TwilioClient($sid, $token);
    //         $message = $client->messages->create(
    //             'whatsapp:' . $noHp,
    //             [
    //                 'from' => $whatsappFrom,
    //                 'body' => $body,
    //                 "mediaUrl" => ["qrImageUrl"],

    //             ]
    //         );

    //         return response()->json(['status' => 'success', 'message_sid' => $message->sid], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
    //     }
    // }
    public function sendMessage($orderId)
    {
        $charge = Charge::with(['siswa.kelas'])->where('order_id', $orderId)->first();
        if (! $charge) {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        $categoryname = $charge->kategori_pembayaran->name;
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $siswa = $charge->siswa->first();
        $kelas = $siswa->kelas->first();
        $grossAmount = intval($charge->gross_amount);
        $namaSiswa = $siswa->name;
        $kelasSiswa = $kelas ? $kelas->name : 'Tidak diketahui';
        $noHp = '+62' . ltrim($siswa->no_hp ?? '85349734475', '0');

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

        if (! $sid || ! $token || ! $whatsappFrom) {
            return response()->json(['status' => 'error', 'message' => 'Konfigurasi Twilio tidak lengkap.'], 500);
        }

        try {
            $qrImageUrl = $charge->url_action;

            // Ambil isi gambar dari URL
            $contents = file_get_contents($qrImageUrl);

            // Pastikan folder penyimpanan QR ada
            $folderPath = public_path('storage/img/waqr/spp');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            // Simpan QR code ke file
            $fileName = 'qr-siswa-' . \Str::slug($charge->name) . '-' . $monthName . '.png';

            $filePath = $folderPath . '/' . $fileName;
            file_put_contents($filePath, $contents);

            // Buat URL publik ke gambar
            $publicUrl = url('storage/img/waqr/spp/' . $fileName);



            // Isi pesan WhatsApp
            $body = "Assalamu'alaikum Warahmatullahi Wabarakatuh.  \n\n"
                . "Yth. Ayah/Bunda Wali dari ananda *$namaSiswa* (*$kelasSiswa*),  \n\n"
                . "Izin kami sampaikan bahwa terdapat tagihan pembayaran pendidikan dengan rincian sebagai berikut: \n\n"
                . "📌 *Kategori Pembayaran*: $categoryname \n"
                . "💰 *Jumlah Tagihan*: Rp " . number_format($grossAmount, 0, ',', '.') . "\n"
                . " Bulan : $monthName \n"
                . "Untuk kemudahan transaksi, silakan melakukan pembayaran dengan memindai QR Code berikut: \n"
                // . "$publicUrl \n\n"
                . "🕊️ Mohon melakukan pembayaran tepat waktu demi kelancaran proses belajar-mengajar.  \n"
                . "Apabila telah melakukan pembayaran, Ayah/Bunda tidak perlu membalas pesan ini.\n\n"
                . "Terima kasih atas perhatian dan kerjasamanya.  \n"
                . "Wassalamu'alaikum Warahmatullahi Wabarakatuh. \n";

            // Kirim pesan WA via Twilio
            $client = new TwilioClient($sid, $token);
            $message = $client->messages->create(
                'whatsapp:' . $noHp,
                [
                    'from' => $whatsappFrom,
                    'body' => $body,
                    // 'mediaUrl' => ["$publicUrl"],
                    'mediaUrl' => ["https://sdmuhammadiyah3smd.com/storage/img/waqr/spp/qr-siswa-SPP%20Juli%20Abdillah%20Abqari%20Agam-Juli.png"],
                ]
            );

            return response()->json(['status' => 'success', 'message_sid' => $message->sid], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }



    public function webhook(Request $request)
    {
        try {
            // Validasi request dari Twilio (opsional tapi recommended)
            if (!$this->validateTwilioRequest($request)) {
                Log::warning('Invalid Twilio webhook request', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Log semua data yang diterima
            Log::info('Twilio webhook received', [
                'all_data' => $request->all(),
                'timestamp' => now()->toISOString()
            ]);

            // Ambil data dari webhook
            $messageSid = $request->input('MessageSid');
            $messageStatus = $request->input('MessageStatus');
            $errorCode = $request->input('ErrorCode');
            $errorMessage = $request->input('ErrorMessage');
            $from = $request->input('From');
            $to = $request->input('To');
            $body = $request->input('Body');
            $numSegments = $request->input('NumSegments', 1);
            $numMedia = $request->input('NumMedia', 0);
            $accountSid = $request->input('AccountSid');
            $messagingServiceSid = $request->input('MessagingServiceSid');
            $apiVersion = $request->input('ApiVersion');

            // Handle berdasarkan status pesan
            switch ($messageStatus) {
                case 'queued':
                    $this->handleQueuedStatus($messageSid, $request->all());
                    break;

                case 'sent':
                    $this->handleSentStatus($messageSid, $request->all());
                    break;

                case 'delivered':
                    $this->handleDeliveredStatus($messageSid, $request->all());
                    break;

                case 'read':
                    $this->handleReadStatus($messageSid, $request->all());
                    break;

                case 'failed':
                    $this->handleFailedStatus($messageSid, $request->all());
                    break;

                case 'undelivered':
                    $this->handleUndeliveredStatus($messageSid, $request->all());
                    break;

                default:
                    Log::info('Unknown message status received', [
                        'status' => $messageStatus,
                        'message_sid' => $messageSid
                    ]);
            }

            // Handle incoming messages (balasan dari user)
            if ($request->has('Body') && !empty($body)) {
                $this->handleIncomingMessage($request->all());
            }

            // Handle media messages
            if ($numMedia > 0) {
                $this->handleMediaMessage($request->all());
            }

            // Simpan ke database (opsional)
            $this->saveWebhookLog($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Validasi bahwa request benar-benar dari Twilio
     */
    private function validateTwilioRequest(Request $request): bool
    {
        $authToken = env('TWILIO_AUTH_TOKEN');

        if (!$authToken) {
            return false;
        }

        $validator = new RequestValidator($authToken);
        $signature = $request->header('X-Twilio-Signature', '');
        $url = $request->fullUrl();
        $params = $request->all();

        return $validator->validate($signature, $url, $params);
    }

    /**
     * Handle status: queued
     */
    private function handleQueuedStatus($messageSid, $data)
    {
        Log::info('Message queued for delivery', [
            'message_sid' => $messageSid,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle status: sent
     */
    private function handleSentStatus($messageSid, $data)
    {
        Log::info('Message sent successfully', [
            'message_sid' => $messageSid,
            'to' => $data['To'] ?? null,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle status: delivered
     */
    private function handleDeliveredStatus($messageSid, $data)
    {
        Log::info('Message delivered successfully', [
            'message_sid' => $messageSid,
            'to' => $data['To'] ?? null,
            'timestamp' => now()->toISOString()
        ]);

        // Update status di database jika diperlukan
        // Contoh: update status pembayaran jadi "notified"
        try {
            // Cari charge berdasarkan phone number atau cara lain
            // $charge = Charge::whereHas('siswa', function($query) use ($data) {
            //     $phoneNumber = str_replace(['whatsapp:', '+62'], ['', '0'], $data['To']);
            //     $query->where('no_hp', 'like', '%' . $phoneNumber . '%');
            // })->latest()->first();

            // if ($charge) {
            //     $charge->notification_status = 'delivered';
            //     $charge->save();
            // }
        } catch (\Exception $e) {
            Log::error('Failed to update charge status', [
                'error' => $e->getMessage(),
                'message_sid' => $messageSid
            ]);
        }
    }

    /**
     * Handle status: read
     */
    private function handleReadStatus($messageSid, $data)
    {
        Log::info('Message read by recipient', [
            'message_sid' => $messageSid,
            'to' => $data['To'] ?? null,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Handle status: failed
     */
    private function handleFailedStatus($messageSid, $data)
    {
        Log::error('Message delivery failed', [
            'message_sid' => $messageSid,
            'error_code' => $data['ErrorCode'] ?? null,
            'error_message' => $data['ErrorMessage'] ?? null,
            'to' => $data['To'] ?? null,
            'timestamp' => now()->toISOString()
        ]);

        // Kirim notifikasi ke admin atau coba kirim ulang
        $this->handleDeliveryFailure($messageSid, $data);
    }

    /**
     * Handle status: undelivered
     */
    private function handleUndeliveredStatus($messageSid, $data)
    {
        Log::warning('Message undelivered', [
            'message_sid' => $messageSid,
            'error_code' => $data['ErrorCode'] ?? null,
            'error_message' => $data['ErrorMessage'] ?? null,
            'to' => $data['To'] ?? null,
            'timestamp' => now()->toISOString()
        ]);

        // Handle undelivered message
        $this->handleDeliveryFailure($messageSid, $data);
    }

    /**
     * Handle incoming messages (balasan dari user)
     */
    private function handleIncomingMessage($data)
    {
        $from = $data['From'] ?? null;
        $body = $data['Body'] ?? null;
        $messageSid = $data['MessageSid'] ?? null;

        Log::info('Incoming message received', [
            'from' => $from,
            'body' => $body,
            'message_sid' => $messageSid,
            'timestamp' => now()->toISOString()
        ]);

        // Extract phone number
        $phoneNumber = str_replace(['whatsapp:', '+62'], ['', '0'], $from);

        // Auto-reply logic (opsional)
        if (stripos($body, 'status') !== false) {
            $this->sendAutoReply($from, "Terima kasih atas pesan Anda. Untuk mengecek status pembayaran, silakan hubungi admin sekolah.");
        } elseif (stripos($body, 'help') !== false || stripos($body, 'bantuan') !== false) {
            $this->sendAutoReply($from, "Bantuan:\n- Ketik 'status' untuk info pembayaran\n- Hubungi admin: 0812-3456-7890");
        } else {
            // Forward ke admin atau simpan untuk review
            Log::info('Message needs manual review', [
                'from' => $from,
                'body' => $body
            ]);
        }
    }

    /**
     * Handle media messages
     */
    private function handleMediaMessage($data)
    {
        $numMedia = (int) ($data['NumMedia'] ?? 0);

        Log::info('Media message received', [
            'from' => $data['From'] ?? null,
            'num_media' => $numMedia,
            'message_sid' => $data['MessageSid'] ?? null
        ]);

        // Process each media item
        for ($i = 0; $i < $numMedia; $i++) {
            $mediaUrl = $data["MediaUrl$i"] ?? null;
            $mediaContentType = $data["MediaContentType$i"] ?? null;

            if ($mediaUrl) {
                Log::info("Media item $i", [
                    'url' => $mediaUrl,
                    'content_type' => $mediaContentType
                ]);

            }
        }
    }

    /**
     * Send auto-reply
     */
    private function sendAutoReply($to, $message)
    {
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $whatsappFrom = env('TWILIO_WHATSAPP_FROM');

            $client = new TwilioClient($sid, $token);
            $response = $client->messages->create($to, [
                'from' => $whatsappFrom,
                'body' => $message
            ]);

            Log::info('Auto-reply sent', [
                'to' => $to,
                'message_sid' => $response->sid
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send auto-reply', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle delivery failures
     */
    private function handleDeliveryFailure($messageSid, $data)
    {
        $errorCode = $data['ErrorCode'] ?? null;
        $to = $data['To'] ?? null;

        // Mapping error codes ke actions
        switch ($errorCode) {
            case '63016': // Phone number is not a valid WhatsApp endpoint
                Log::error('Invalid WhatsApp number', [
                    'phone' => $to,
                    'message_sid' => $messageSid
                ]);
                break;

            case '63017': // Phone number has opted out of WhatsApp
                Log::error('Phone number opted out', [
                    'phone' => $to,
                    'message_sid' => $messageSid
                ]);
                break;

            case '63018': // Message content not allowed
                Log::error('Message content not allowed', [
                    'phone' => $to,
                    'message_sid' => $messageSid
                ]);
                break;

            default:
                Log::error('Unknown delivery error', [
                    'error_code' => $errorCode,
                    'phone' => $to,
                    'message_sid' => $messageSid
                ]);
        }

        // Kirim notifikasi ke admin
        $this->notifyAdminOfFailure($messageSid, $data);
    }

    /**
     * Notify admin of delivery failure
     */
    private function notifyAdminOfFailure($messageSid, $data)
    {
        $adminPhone = env('ADMIN_WHATSAPP_NUMBER'); // Set di .env

        if ($adminPhone) {
            $message = "⚠️ GAGAL KIRIM PESAN\n\n"
                     . "Message SID: $messageSid\n"
                     . "Tujuan: " . ($data['To'] ?? 'Unknown') . "\n"
                     . "Error: " . ($data['ErrorMessage'] ?? 'Unknown error') . "\n"
                     . "Waktu: " . now()->format('d/m/Y H:i:s');

            $this->sendAutoReply('whatsapp:+62' . ltrim($adminPhone, '0'), $message);
        }
    }

    /**
     * Save webhook log to database (opsional)
     */
    private function saveWebhookLog($data)
    {
        try {
            // Uncomment jika ingin menyimpan log ke database
            /*
            WhatsappLog::create([
                'message_sid' => $data['MessageSid'] ?? null,
                'message_status' => $data['MessageStatus'] ?? null,
                'from_number' => $data['From'] ?? null,
                'to_number' => $data['To'] ?? null,
                'body' => $data['Body'] ?? null,
                'error_code' => $data['ErrorCode'] ?? null,
                'error_message' => $data['ErrorMessage'] ?? null,
                'webhook_data' => json_encode($data),
                'received_at' => now()
            ]);
            */
        } catch (\Exception $e) {
            Log::error('Failed to save webhook log', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint untuk testing webhook
     */
    public function testWebhook(Request $request)
    {
        Log::info('Test webhook called', [
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Test webhook received',
            'timestamp' => now()->toISOString()
        ]);
    }
}
