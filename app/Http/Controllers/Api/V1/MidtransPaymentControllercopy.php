<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MidtransPaymentController extends Controller
{
    public function callback(Request $request)
    {
        // Return OK jika request GET (health check)
        if ($request->isMethod('GET')) {
            return response()->json(['message' => 'OK'], 200);
        }

        try {
            // Ambil raw JSON body untuk mencegah parsing error
            $rawBody = $request->getContent();
            $midtransResponse = json_decode($rawBody, true) ?? $request->all();

            // Log untuk debugging
            Log::info('Midtrans Callback Received', [
                'headers' => $request->headers->all(),
                'body' => $midtransResponse,
            ]);

            // Validasi Signature Key (opsional, tapi disarankan)
            if (isset($midtransResponse['order_id'], $midtransResponse['status_code'], $midtransResponse['gross_amount'], $midtransResponse['signature_key'])) {
                $expectedSignature = hash('sha512',
                    $midtransResponse['order_id'] .
                    $midtransResponse['status_code'] .
                    $midtransResponse['gross_amount'] .
                    env('MIDTRANS_SERVER_KEY')
                );

                if ($midtransResponse['signature_key'] !== $expectedSignature) {
                    return response()->json(['message' => 'Invalid signature'], 403);
                }
            }

            // Simpan log error jika status_code tertentu
            if (isset($midtransResponse['status_code']) && in_array($midtransResponse['status_code'], ['202', '300', '401', '405'])) {
                DB::table('error_log')->insert([
                    'status_code' => $midtransResponse['status_code'],
                    'error' => $midtransResponse['status_message'] ?? 'Unknown error',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Jika order_id tidak ada
            if (! isset($midtransResponse['order_id'])) {
                return response()->json(['message' => 'Invalid request, order_id not found'], 400);
            }

            // Persiapan data transaksi
            $data = [
                'transaction_status' => $midtransResponse['transaction_status'] ?? null,
                'transaction_id' => $midtransResponse['transaction_id'] ?? null,
                'transaction_time' => $midtransResponse['transaction_time'] ?? null,
                'fraud_status' => $midtransResponse['fraud_status'] ?? 'accept',
            ];

            // Mapping payment type
            if (isset($midtransResponse['payment_type'])) {
                switch ($midtransResponse['payment_type']) {
                    case 'bank_transfer':
                        $data['bank'] = $midtransResponse['va_numbers'][0]['bank'] ?? null;
                        $data['va_number'] = $midtransResponse['va_numbers'][0]['va_number'] ?? null;
                        break;
                    case 'credit_card':
                        $data['bank'] = $midtransResponse['bank'] ?? null;
                        break;
                    case 'qris':
                        $data['bank'] = $midtransResponse['acquirer'] ?? null;
                        break;
                    case 'gopay':
                    case 'shopeepay':
                        $data['bank'] = $midtransResponse['issuer'] ?? null;
                        break;
                    case 'cstore':
                        $data['bank'] = $midtransResponse['store'] ?? null;
                        $data['va_number'] = $midtransResponse['payment_code'] ?? null;
                        break;
                    default:
                        return response()->json(['message' => 'Unsupported payment type'], 400);
                }
            }

            // Cari charge berdasarkan order_id
            $charge = Charge::where('order_id', $midtransResponse['order_id'])
                ->orWhere('order_id_1', $midtransResponse['order_id'])
                ->first();

            // Jika charge tidak ditemukan, simpan ke charge_not_found
            if (! $charge) {
                \DB::table('charge_not_found')->updateOrInsert(
                    [
                        'order_id' => $midtransResponse['order_id'] ?? null,
                    ],
                    [
                        'id' => Str::uuid(),
                        'transaction_type' => $midtransResponse['transaction_type'] ?? null,
                        'transaction_time' => $midtransResponse['transaction_time'] ?? null,
                        'transaction_status' => $midtransResponse['transaction_status'] ?? null,
                        'transaction_id' => $midtransResponse['transaction_id'] ?? null,
                        'status_message' => json_encode($midtransResponse['status_message'] ?? null),
                        'status_code' => $midtransResponse['status_code'] ?? null,
                        'signature_key' => $midtransResponse['signature_key'] ?? null,
                        'settlement_time' => $midtransResponse['settlement_time'] ?? null,
                        'payment_type' => $midtransResponse['payment_type'] ?? null,
                        'metadata' => json_encode($midtransResponse['metadata'] ?? null),
                        'merchant_id' => $midtransResponse['merchant_id'] ?? null,
                        'issuer' => $midtransResponse['issuer'] ?? null,
                        'gross_amount' => $midtransResponse['gross_amount'] ?? null,
                        'fraud_status' => $midtransResponse['fraud_status'] ?? null,
                        'expiry_time' => $midtransResponse['expiry_time'] ?? null,
                        'currency' => $midtransResponse['currency'] ?? null,
                        'acquirer' => $midtransResponse['acquirer'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                return response()->json(['message' => 'Charge not found in database and saved'], 200);
            }

            // Normalisasi status transaksi
            if (in_array($data['transaction_status'], ['settlement', 'capture'])) {
                $data['transaction_status'] = 'settlement';
            }

            // Jika status dari Midtrans settlement/capture, cek status lama di Midtrans
            if (in_array($midtransResponse['transaction_status'], ['settlement', 'capture'])) {
                try {
                    $server_key = env('MIDTRANS_SERVER_KEY');
                    $client = new Client;
                    $check_transaction = $client->get("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/status", [
                        'headers' => [
                            'Authorization' => 'Basic '.base64_encode($server_key.':'),
                        ],
                    ]);

                    $transaction_status = json_decode($check_transaction->getBody(), true);

                    if ($transaction_status['transaction_status'] === 'expire') {
                        $cancelResponse = $client->post("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/cancel", [
                            'headers' => [
                                'Authorization' => 'Basic '.base64_encode($server_key.':'),
                            ],
                        ]);

                        $cancelData = json_decode($cancelResponse->getBody(), true);
                        if (isset($cancelData['status_code']) && in_array($cancelData['status_code'], [200, 201])) {
                            return response()->json(['message' => 'Transaksi lama berhasil dibatalkan'], 200);
                        }
                    }
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Error saat membatalkan transaksi lama: '.$e->getMessage()], 500);
                }
                $data['transaction_status'] = 'settlement';
            }

            // Jika charge sudah expire/cancel, tidak update settlement
            if (in_array($charge->transaction_status, ['expire', 'cancel'])) {
                $data['transaction_status'] = 'expire';
            }

            // Logging aktivitas pembayaran
            $siswaName = $charge->siswa->name ?? 'Unknown Student';
            if (in_array($midtransResponse['transaction_status'], ['settlement', 'capture'])) {
                $status = 'settlement';
                activity()
                    ->useLog('default')
                    ->tap(function ($activity) {
                        $activity->causer_id = auth()->id() ?? Str::uuid();
                        $activity->causer_type = 'Midtrans';
                    })
                    ->log("Murid: {$siswaName} Melakukan Pembayaran Dengan Status {$status} Pada Order ID: {$charge->order_id}");
            }

            // Update data charge
            $charge->update($data);

            return response()->json(['message' => 'Payment data updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
        }
    }

    public function update_transaction_status($charge, $status)
    {
        $client = new Client;
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            $response = $client->post("https://api.midtrans.com/v2/{$charge->order_id}/cancel", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic '.base64_encode("$server_key:"),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Jika status transaksi tidak bisa diubah
            if (isset($responseData['status_code']) && $responseData['status_code'] == '412') {
                DB::table('error_log')->insert([
                    'error' => $responseData['status_message'],
                    'status_code' => $responseData['status_code'],
                ]);

                return true;
            }

            return $responseData['status_code'] == 200 || $responseData['status_code'] == 201;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status Midtrans: '.$e->getMessage()], 500);
        }
    }

    public function callback_unfinish(Request $request)
    {
        // Jika dibutuhkan, bisa diisi logika untuk transaksi yang tidak selesai
        return response()->json(['message' => 'Unfinish callback received'], 200);
    }

    public function callback_error(Request $request)
    {
        // Jika dibutuhkan, bisa diisi logika untuk transaksi yang error
        return response()->json(['message' => 'Error callback received'], 200);
    }
}
