<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtransPaymentController extends Controller
{
    public function callback(Request $request)
    {
        $midtransResponse = $request->all();

        // Pastikan order_id tersedia
        if (!isset($midtransResponse['order_id'])) {
            return response()->json(['message' => 'Invalid request, order_id not found'], 400);
        }

        // Ambil data dari response Midtrans
        $data = [
            'transaction_status' => $midtransResponse['transaction_status'] ?? null,
            'transaction_id' => $midtransResponse['transaction_id'] ?? null,
            'transaction_time' => $midtransResponse['transaction_time'] ?? null,
            'fraud_status' => $midtransResponse['fraud_status'] ?? 'accept',
        ];

        // Pastikan payment_type ada dalam response
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

                case 'echannel': // Mandiri Bill Payment
                    $data['bank'] = 'mandiri';
                    $data['va_number'] = $midtransResponse['bill_key'] ?? null;
                    break;

                case 'cstore': // Indomaret / Alfamart
                    $data['bank'] = $midtransResponse['store'] ?? null;
                    $data['va_number'] = $midtransResponse['payment_code'] ?? null;
                    break;

                default:
                    return response()->json(['message' => 'Unsupported payment type'], 400);
            }
        }

        // Update data di tabel charges berdasarkan order_id
        $charge = Charge::where('order_id', $midtransResponse['order_id'])
                    ->orWhere('order_id_1', $midtransResponse['order_id'])
                    ->first();

        if (!$charge) {
            return response()->json(['message' => 'Charge data not found'], 404);
        }

        if($charge->transaction_status == 'settlement' || $charge->transaction_status == 'capture'){
            // cancel order_id
            $server_key = env('MIDTRANS_SERVER_KEY');
            $client = new Client();
            $response = $client->get("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/cancel", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            if($midtransResponse['transaction_status'] == 'settlement' || $midtransResponse['transaction_status'] == 'capture'){
                $data['transaction_status'] = 'settlement';
            }
        }


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
                throw new \Exception($responseData['status_message']);
            }

            return ($responseData['status_code'] == 200 || $responseData['status_code'] == 201);
        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui status Midtrans: ' . $e->getMessage());
            return false;
        }
    }


    public function webhook(Request $request)
    {
        $data = $request->all();
        return response()->json(['data' => 'data']);
    }

    public function callback_unfinish(Request $request)
    {

    }

    public function callback_error(Request $request)
    {

    }
}
