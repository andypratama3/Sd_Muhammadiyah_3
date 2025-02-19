<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $list_pembayaran = collect();
        $siswa = collect();

        if ($request->filled('nisn')) {
            $request->validate([
                'nisn' => 'required|string',
            ]);

            $siswa = Siswa::where('nisn', $request->nisn)
                // ->orWhere('name', 'like', '%' . $request->nisn . '%')
                ->first();


            if (!$siswa) {
                $list_pembayaran = collect();
                $siswa = null;
            } else {
                $list_pembayaran = Charge::where('siswa_id', $siswa->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->format('Y'); // Group by Year
                })
                ->map(function ($yearGroup) {
                    return $yearGroup->groupBy('category_payment_id'); // Group by Category
                });
            }
        }

        // dd($siswa, $list_pembayaran);

        return view('profil.pembayaran.index', compact('list_pembayaran', 'siswa'));
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
        if (!$charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Charge tidak ditemukan',
            ]);
        }

        $siswa = Siswa::find($charge->siswa_id);
        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ]);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            // Jika Snap Token sudah ada, langsung kembalikan
            if (!empty($charge->snap_token)) {
                return response()->json([
                    'status' => 'success',
                    'snap_token' => $charge->snap_token,
                ]);
            }

            // Ambil data transaksi dari Midtrans
            $response = $client->get("https://api.sandbox.midtrans.com/v2/{$charge->order_id}/status", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Pastikan respons memiliki order_id dan gross_amount
            if (!isset($responseData['order_id'], $responseData['gross_amount'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data transaksi tidak lengkap dari Midtrans',
                    'data' => $responseData,
                ]);
            }

            // new order_id & transaction_id

            $params = [
                'transaction_details' => [
                    'order_id' => $charge->id,
                    'gross_amount' => $charge->gross_amount,
                ],
                'item_details' => [
                    [
                        'id' => $charge->id,
                        'price' => $charge->gross_amount,
                        'quantity' => 1,
                        'name' => $charge->name,
                    ]
                ],

            ];


            // Generate Snap Token baru
            try {
                $snapToken = Snap::getSnapToken($params);
                $charge->snap_token = $snapToken;
                $charge->order_id_1 = $charge->id;
                $charge->save();


                return response()->json([
                    'status' => 'success',
                    'snap_token' => $snapToken,
                    'data' => $params,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal membuat Snap Token: ' . $e->getMessage(),
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
        if (!$charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Charge tidak ditemukan',
            ]);
        }

        $siswa = Siswa::find($charge->siswa_id);
        if (!$siswa) {
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
}

