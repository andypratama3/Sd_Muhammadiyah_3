<?php

namespace App\Http\Controllers\Api\V1;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MidtransPaymentController extends Controller
{
    public function callback(Request $request)
    {
        $charge = Charge::where('order_id',$request->order_id)->first();

        if($charge->isEmpty())
        {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        $status_transaction = $request->transaction_status;

        if($status_transaction == 'settlement' || $status_transaction == 'capture'){
            $charge = $charge->transaction_status = 'settlement';
        }elseif($status_transaction == 'pending'){
            $charge = $charge->transaction_status = $request->transaction_status;
        }elseif($status_transaction == 'deny'){
            $charge = $charge->transaction_status = $request->transaction_status;
        }else{
            $charge = $charge->transaction_status = $request->transaction_status;
        }

        $change_transaction_status = $charge->save();

        if($change_transaction_status){

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction status updated',
                'data' => $charge,
            ]);
        }
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
