<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('profil.pembayaran.index');
    }
    public function getOrder(Request $request)
    {
        $kode_pembayaran = $request->kode_pembayaran;
        $pembayaran = Pembayaran::where('order_id', $kode_pembayaran)->first();
        $kelas = $pembayaran->kelas->name;
        $siswa = $pembayaran->siswa->name;

        if ($pembayaran) {
            // Return a JSON response with a 200 status code and the data
            return response()->json(
                [
                    'data' => $pembayaran,
                    'siswa' => $siswa,
                    'kelas' => $kelas,
                    'message' => 'Berhasil Mendapatkan Order ID'
                ], 200);
        } else {

            return response()->json(['message' => 'Tidak Ada Data Pembayaran']);
        }
    }
    public function pay(Request $request)
    {
        $order_id = $request->order_id;
        $pembayaran = Pembayaran::where('order_id', $order_id)->first();
        // SAMPLE HIT API iPaymu v2 PHP
        $va           = '0000002217160075'; //get on iPaymu dashboard
        $apiKey       = 'SANDBOX9CAEF80A-BEE1-40DD-A0E4-A71F36393097'; //get on iPaymu dashboard

        $url          = 'https://sandbox.ipaymu.com/api/v2/payment'; // for development mode
        // $url          = 'https://my.ipaymu.com/api/v2/payment'; // for production mode

        $method       = 'POST'; //method

        //Request Body//
        $body['product']    = array($pembayaran->name);
        $body['price']      = array($pembayaran->gross_amount);
        $body['qty']      = array('1');
        $body['returnUrl']  = 'https://your-website.com/cancel-page';
        $body['cancelUrl']  = 'https://your-website.com/cancel-page';
        $body['notifyUrl']  =  route('ipaymu.api.callback');
        // $body['referenceId'] = '1234'; //your reference id
        //End Request Body//

        //Generate Signature
        // *Don't change this
        $jsonBody     = json_encode($body, JSON_UNESCAPED_SLASHES);
        $requestBody  = strtolower(hash('sha256', $jsonBody));
        $stringToSign = strtoupper($method) . ':' . $va . ':' . $requestBody . ':' . $apiKey;
        $signature    = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp    = Date('YmdHis');
        //End Generate Signature


        $ch = curl_init($url);

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'va: ' . $va,
            'signature: ' . $signature,
            'timestamp: ' . $timestamp
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POST, count($body));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $err = curl_error($ch);
        $ret = curl_exec($ch);

        $response = json_decode($ret, true);

        if (isset($response['Data']['Url']) && isset($response['Data']['SessionID'])) {
            $pembayaran->SessionID = $response['Data']['SessionID'];
            $pembayaran->Url = $response['Data']['Url'];
            $redirectUrl = $response['Data']['Url'];
            $pembayaran->update();

            return response()->json([
                'redirect' => $redirectUrl,'Akan Mengalihkan'], 200);
        } else {
            // Handle the case where the response does not contain the expected URL
            return response()->json([
                'message' => 'Failed to get the redirect URL from the response',
            ], 500);
        }

        curl_close($ch);
    }

}
