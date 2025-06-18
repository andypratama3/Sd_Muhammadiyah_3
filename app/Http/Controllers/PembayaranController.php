<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Siswa;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $list_pembayaran = collect();
        $siswa = null;
        $nisn_plain = null;

        if ($request->filled('nisn')) {
            $nisn = $request->nisn;

            try {
                // Try to decrypt the nisn
                $nisn_decrypted = Crypt::decryptString($request->nisn);
            } catch (\Exception $e) {
                // If decryption fails, encrypt the nisn and redirect
                $encrypted = Crypt::encryptString($request->nisn);

                return redirect()->route('pembayaran.index', ['nisn' => $encrypted]);
            }

            // Lanjut cari siswa
            $siswa = Siswa::where('nisn', $nisn_decrypted)->first();

            // Assign the decrypted nisn to nisn_plain
            $nisn_plain = $nisn_decrypted;

            if ($siswa) {
                $list_pembayaran = Charge::where('siswa_id', $siswa->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy(function ($item) {
                        return \Carbon\Carbon::parse($item->created_at)->format('Y');
                    })
                    ->map(function ($yearGroup) {
                        return $yearGroup->groupBy('category_payment_id');
                    });
            }
        }

        return view('profil.pembayaran.index', compact('nisn_plain', 'list_pembayaran', 'siswa'));
    }

    // public function searchOrder()
    // {

    // }

    public function searchOrder(Request $request)
    {
        $request->validate([
            'charge_id' => 'required',
        ]);

        $charge = Charge::find($request->charge_id);
        if (! $charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Charge tidak ditemukan',
            ]);
        }

        $siswa = Siswa::find($charge->siswa_id);
        if (! $siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ]);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $client = new Client;
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            // Ambil data transaksi dari Midtrans
            $response = $client->get("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/status", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($server_key.':'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (! isset($responseData['order_id'], $responseData['gross_amount'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data transaksi tidak lengkap dari Midtrans',
                    'data' => $responseData,
                ]);
            }

            $order_id = Str::uuid();
            $biaya = 5000;
            $gross_amount = $charge->gross_amount - $biaya;

            // Persiapan parameter Snap Token
            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => $gross_amount + $biaya,
                ],
                'item_details' => [
                    [
                        'id' => 1,
                        'price' => $gross_amount,
                        'quantity' => 1,
                        'name' => $charge->name,
                    ],
                    [
                        'id' => 2,
                        'name' => 'Biaya Administrasi Sekolah Kreatif SD Muhammadiyah 3 Samarinda',
                        'price' => $biaya,
                        'quantity' => 1,
                    ],

                ],
                'expiry' => [
                    'unit' => 'days',
                    // "unit" => "minutes",
                    'duration' => 20,
                ],

            ];

            if ($responseData['transaction_status'] === 'expire' || $responseData['transaction_status'] === 'pending' || $responseData['transaction_status'] === 'cancel' || empty($charge->snap_token)) {
                // Jika expired atau snap_token kosong, buat token baru
                $snapToken = Snap::getSnapToken($params);
                $charge->snap_token = $snapToken;
                $charge->order_id_1 = $order_id;
                $charge->update();

                // dd($snapToken);

                return response()->json([
                    'status' => 'success',
                    'snap_token' => $snapToken,
                    'data' => $params,
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'snap_token' => $charge->snap_token,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function searchOrderDetail(Request $request)
    {
        $request->validate([
            'charge_id' => 'required',
        ]);

        $charge = Charge::find($request->charge_id);
        if (! $charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Charge tidak ditemukan',
            ]);
        }

        $siswa = Siswa::find($charge->siswa_id);
        if (! $siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $charge,
        ]);
    }

    public function howToPay()
    {
        return view('profil.pembayaran.howtopay');
    }

    public function downloadQr($id)
    {
        $charge = Charge::findOrFail($id);
        $url = $charge->url_action;

        $contents = file_get_contents($url);

        return response($contents)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-siswa-'.$charge->name.'.png"');
    }
}
