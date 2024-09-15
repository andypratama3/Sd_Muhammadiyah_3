<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Siswa;
use App\Models\Charge;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        return view('profil.pembayaran.index');
    }

    public function searchOrder(Request $request)
    {
        if ($request->has('kode')) {
            $kode = $request->input('kode');

            // Search for the payment by order_id in Charge or name in Siswa
            $payment = Charge::where('order_id', $kode)
                        ->orWhereHas('siswa', function ($query) use ($kode) {
                            $query->where('name', 'LIKE', "%{$kode}%");
                        })->first();

            if ($payment) {
                // Fetch the related siswa data
                $siswa = Siswa::find($payment->siswa_id);

                // Configure Midtrans
                Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                Config::$isProduction = false; // Set to true in production
                Config::$isSanitized = true;
                Config::$is3ds = true;

                // Check if snap_token exists
                if ($payment->snap_token) {
                    // Use the existing snap_token
                    $snapToken = $payment->snap_token;
                } else {
                    // Prepare parameters for Snap if snap_token does not exist
                    $params = [
                        'transaction_details' => [
                            'order_id' => $payment->order_id,
                            'gross_amount' => $payment->gross_amount,
                        ],
                        'customer_details' => [
                            'first_name' => $siswa->name,
                            'email' => $siswa->email,
                            'phone' => $siswa->no_hp,
                        ],
                        'item_details' => [
                            [
                                'id' => $payment->order_id,
                                'price' => $payment->gross_amount,
                                'quantity' => 1,
                                'name' => $payment->name,
                            ]
                        ],
                        // "expiry" => [
                        //     "start_time" => now(),
                        //     "unit" => "minutes",
                        //     "duration" => 180
                        // ],
                    ];
                    // Try generating the snap token
                    try {
                        $snapToken = Snap::getSnapToken($params);

                        // Save the new snap_token to the database
                        $payment->snap_token = $snapToken;
                        $payment->save();
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Failed to create Snap token: ' . $e->getMessage(),
                        ]);
                    }
                }

                // Return success response with snap_token
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'siswa' => $siswa,
                        'order_id' => $payment->order_id,
                        'gross_amount' => $payment->gross_amount,
                        'name' => $payment->name,
                        'transaction_status' => $payment->transaction_status,
                    ],
                    'snap_token' => $snapToken,
                ]);
            } else {
                // Payment not found
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembayaran tidak ditemukan',
                ]);
            }
        }
    }
}
