<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Siswa;
use App\Models\Charge;
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
            'kode' => 'required|string',
        ]);

        $currentMonth = Carbon::now()->startOfMonth();

        if ($request->has('kode')) {
            $kode = $request->input('kode');
            // Search for the payment by order_id in Charge or name in Siswa
            $payment = Charge::where('order_id', $kode)
                // ->orWhere('order_id', 'LIKE', "%{$kode}%")
                ->orWhere('va_number', 'LIKE', "%{$kode}%")
                ->whereMonth('transaction_time', Carbon::now()->month)
                ->whereYear('transaction_time', Carbon::now()->year)
                ->orWhereHas('siswa', function ($query) use ($kode) {
                    $query->where('name', 'LIKE', "%{$kode}%")
                        ->whereMonth('transaction_time', Carbon::now()->month)
                        ->whereYear('transaction_time', Carbon::now()->year);
                })
                ->first();


                if ($payment !== null) {
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

    public function snap_url($order_id)
    {
        $token = env('MIDTRANS_SERVER_KEY');
        $charge = Charge::where('order_id', $order_id)->firstOrFail();

        if(!$charge){
            abort(404);
        }

        return view('midtrans.snap', compact('token','charge'));
    }
}
