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
        $midtransResponse = $request->all();

        // Pastikan status_code tersedia sebelum memproses
        if (isset($midtransResponse['status_code']) && in_array($midtransResponse['status_code'], ['202', '300', '401', '405'])) {
            DB::table('error_log')->insert([
                'status_code' => $midtransResponse['status_code'],
                'error' => $midtransResponse['status_message'] ?? 'Unknown error',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Validasi order_id
        if (!isset($midtransResponse['order_id'])) {
            return response()->json(['message' => 'Invalid request, order_id not found'], 400);
        }

        // Inisialisasi data transaksi
        $data = [
            'transaction_status' => $midtransResponse['transaction_status'] ?? null,
            'transaction_id' => $midtransResponse['transaction_id'] ?? null,
            'transaction_time' => $midtransResponse['transaction_time'] ?? null,
            'fraud_status' => $midtransResponse['fraud_status'] ?? 'accept',
        ];

        // Menentukan metode pembayaran
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

        // Cari transaksi berdasarkan order_id
        $charge = Charge::where('order_id', $midtransResponse['order_id'])
            ->orWhere('order_id_1', $midtransResponse['order_id'])
            ->first();

        if (!$charge) {
            return response()->json(['message' => 'Charge not found'], 404);
        }

        // Jika transaksi sudah sukses (settlement atau capture), ubah statusnya
        if (in_array($data['transaction_status'], ['settlement', 'capture'])) {
            $data['transaction_status'] = 'settlement';
        }

        // Jika transaksi baru sukses, batalkan transaksi lama
        if (in_array($midtransResponse['transaction_status'], ['settlement', 'capture'])) {
            try {
                $server_key = env('MIDTRANS_SERVER_KEY');
                $client = new Client();
                $cancelResponse = $client->post("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/cancel", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                        'Content-Type' => 'application/json',
                    ],
                ]);


                $cancelData = json_decode($cancelResponse->getBody(), true);
                // Jika transaksi lama berhasil dibatalkan, ubah statusnya
                if ($cancelData['status_code'] == 200 || $cancelData['status_code'] == 201) {
                    return response()->json(['message' => 'Transaksi lama berhasil dibatalkan'], 200);
                }

            } catch (\Exception $e) {
                return response()->json(['message' => 'Error saat membatalkan transaksi lama: ' . $e->getMessage()], 500);
            }

            // Pastikan transaksi baru tetap berstatus settlement
            $data['transaction_status'] = 'settlement';
        }

        // Jika transaksi sebelumnya sudah expired/cancelled, jangan ubah statusnya lagi
        if (in_array($charge->transaction_status, ['expire', 'cancel'])) {
            $data['transaction_status'] = 'expire';
        }

        // Ambil nama siswa jika ada
        $siswaName = $charge->siswa ? $charge->siswa->name : 'Unknown Student';

        // Catat aktivitas pembayaran
        activity()
            ->useLog('default')
            ->tap(function ($activity) {
                $activity->causer_id = auth()->id() ?? null;
                $activity->causer_type = 'Midtrans';
            })
            ->log("Pembayaran {$data['transaction_status']} Pada Order ID: {$charge->order_id} - Murid: {$siswaName}");

        // Update status transaksi di database
        $charge->update($data);

        return response()->json(['message' => 'Payment data updated successfully'], 200);
    }


    public function update_transaction_status($charge, $status)
    {
        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            $response = $client->post("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/cancel", [
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
