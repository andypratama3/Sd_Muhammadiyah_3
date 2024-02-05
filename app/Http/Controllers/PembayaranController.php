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
        $url          = 'https://sandbox.ipaymu.com/api/v2/payment'; // for development mode
        // $url       = 'https://my.ipaymu.com/api/v2/payment'; // for production mode
        $method       = 'POST'; //method

        $body = array([
            'order_id' => $pembayaran->order_id,
        ]);
        //Request Body//
        // $body['product']    = array();
        // $body['qty']        = array('1', '3');
        // $body['price']      = array('100000', '20000');
        // $body['returnUrl']  = 'https://your-website.com/thank-you-page';
        // $body['cancelUrl']  = 'https://your-website.com/cancel-page';
        // $body['notifyUrl']  = 'https://your-website.com/callback-url';
        // $body['referenceId'] = '1234'; //your reference id
        // //End Request Body//

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
        curl_close($ch);

        return response()->json(
            [
                'pembayaran' => $pembayaran,
                'message' => 'Berhasil Mendapatkan Order ID'
            ], 200);

    }
}
