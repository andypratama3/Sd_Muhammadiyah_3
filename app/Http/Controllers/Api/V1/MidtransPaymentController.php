<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtransPaymentController extends Controller
{
    public function callback(Request $request)
    {
        // retrun response oke for receive midtrans notification

        if ($request->isMethod('GET')) {
            return response()->json(['message' => 'OK'], 200);
        }


        try {
            $midtransResponse = $request->all();

            if (isset($midtransResponse['status_code']) && in_array($midtransResponse['status_code'], ['202', '300', '401', '405'])) {
                DB::table('error_log')->insert([
                    'status_code' => $midtransResponse['status_code'],
                    'error' => $midtransResponse['status_message'] ?? 'Unknown error',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!isset($midtransResponse['order_id'])) {
                return response()->json(['message' => 'Invalid request, order_id not found'], 400);
            }

            $data = [
                'transaction_status' => $midtransResponse['transaction_status'] ?? null,
                'transaction_id' => $midtransResponse['transaction_id'] ?? null,
                'transaction_time' => $midtransResponse['transaction_time'] ?? null,
                'fraud_status' => $midtransResponse['fraud_status'] ?? 'accept',
            ];

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

            $charge = Charge::where('order_id', $midtransResponse['order_id'])
                ->orWhere('order_id_1', $midtransResponse['order_id'])
                ->first();

            if (!$charge) {
                return response()->json(['message' => 'Charge not found'], 404);
            }

            if (in_array($data['transaction_status'], ['settlement', 'capture'])) {
                $data['transaction_status'] = 'settlement';
            }

            if (in_array($midtransResponse['transaction_status'], ['settlement', 'capture'])) {
                try {
                    $server_key = env('MIDTRANS_SERVER_KEY');
                    $client = new Client();
                    $mode = config('midtrans.is_production');
                    $check_transaction = $client->get("https://api.midtrans.com/v2/{$charge->order_id}/status", [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                        ],
                    ]);

                    $transaction_status = json_decode($check_transaction->getBody(), true);

                    if ($transaction_status['transaction_status'] === 'expire') {
                        $cancelResponse = $client->post("https://api.midtrans.com/v2/{$charge->order_id}/cancel", [
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                            ],
                        ]);

                        $cancelData = json_decode($cancelResponse->getBody(), true);
                        if (isset($cancelData['status_code']) && in_array($cancelData['status_code'], [200, 201])) {
                            return response()->json(['message' => 'Transaksi lama berhasil dibatalkan'], 200);
                        }
                    }
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Error saat membatalkan transaksi lama: ' . $e->getMessage()], 500);
                }
                $data['transaction_status'] = 'settlement';
            }

            if (in_array($charge->transaction_status, ['expire', 'cancel'])) {
                $data['transaction_status'] = 'expire';
            }

            $siswaName = $charge->siswa->name ?? 'Unknown Student';

            if(in_array($midtransResponse['transaction_status'], ['settlement', 'capture'])) {
                $status = 'settlement';
                activity()
                    ->useLog('default')
                    ->tap(function ($activity) {
                        $activity->causer_id = auth()->id() ?? Str::uuid();
                        $activity->causer_type = 'Midtrans';
                    })
                    // ->log("Pembayaran {$status} Pada Order ID: {$charge->order_id} - Murid: {$siswaName}");
                    ->log("Murid: {$siswaName} Melakukan Pembayaran Dengan Status {$status} Pada Order ID: {$charge->order_id}");
            }


            $charge->update($data);

            return response()->json(['message' => 'Payment data updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }



    public function update_transaction_status($charge, $status)
    {
        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            $response = $client->post("https://api.midtrans.com/v2/{$charge->order_id}/cancel", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode("$server_key:"),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Jika status transaksi tidak bisa diubah
            if (isset($responseData['status_code']) && $responseData['status_code'] == "412") {
                //save meesage error
                DB::table('error_log')->insert([
                    'error' => $responseData['status_message'],
                    'status_code' => $responseData['status_code'],
                ]);

                return true;

                throw new \Exception($responseData['status_message']);
            }

            return ($responseData['status_code'] == 200 || $responseData['status_code'] == 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status Midtrans: ' . $e->getMessage()], 500);
            return false;
        }
    }

    public function callback_unfinish(Request $request)
    {
        //
    }

    public function callback_error(Request $request)
    {

    }
}
