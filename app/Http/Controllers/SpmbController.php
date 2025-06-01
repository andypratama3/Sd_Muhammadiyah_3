<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Spmb;
use Midtrans\Config;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Models\JudulPembayaran;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\V1\MidtransPaymentController;

class SpmbController extends Controller
{
    public function __construct(MidtransPaymentController $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function index()
    {
        // $nomor_urut = Spmb::max('nomor_urut');
        // return view('spmb.index', compact('nomor_urut'));
        return view('spmb.comming_soon');
    }

     public function pay(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Cek apakah sudah ada order_id di session, gunakan jika ad || localstorage
        if ($request->session()->has('spmb_order_id')) {
            $orderId = $request->session()->get('spmb_order_id');
        } else {
            $orderId = (string) Str::uuid();
            $request->session()->put('spmb_order_id', $orderId);
        }

        $judulPembayaran = JudulPembayaran::where('name', 'SPMB')->first();

        $server_key = env('MIDTRANS_SERVER_KEY');
        $client = new Client();
        $mode = config('midtrans.is_production');
        $check_transaction = $client->get("https://api.sandbox.midtrans.com/v2/{$orderId}/status", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
            ],
        ]);

        $transaction_status = json_decode($check_transaction->getBody(), true);

        if (isset($transaction_status['transaction_status']) && in_array($transaction_status['transaction_status'], ['settlement', 'capture'])) {
            return response()->json([
                'status' => 'already_paid',
                'order_id' => $orderId,
                'transaction_status' => $transaction_status['transaction_status'],
            ]);
        }


        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => 305000,
            ],
            'customer_details' => [
                'first_name' => $request->nama ?? 'Pengguna',
                'phone' => $request->phone ?? '0000000000',
            ],
            'item_details' => [
                [
                    'id' => (string) Str::uuid(),
                    'price' => 300000,
                    'quantity' => 1,
                    'name' => ($judulPembayaran ? $judulPembayaran->name : 'SPMB') . ' - ' . ($request->nama ?? ''),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'price' => 5000,
                    'quantity' => 1,
                    'name' => 'Biaya Administrasi Pembayaran',
                ],
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'status' => 'success',
            'snap_token' => $snapToken,
            'order_id' => $orderId,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|string',
            'nama' => 'required|string|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:100',
            'suku' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'nama_asal_sekolah' => 'nullable|string|max:100',
            'sttb' => 'nullable|string|max:100',
            'alamat_sekolah' => 'nullable|string|max:255',
            'select_data' => 'required|in:orang_tua,wali',

            // data orang tua
            'nama_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'nullable|string|max:100',
            'pendidikan_ayah' => 'nullable|string|max:100',
            'alamat_ayah' => 'nullable|string|max:255',
            'pendidikan_ibu' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_ibu' => 'nullable|string|max:255',

            // wali
            'nama_wali' => 'nullable|string|max:100',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'alamat_wali' => 'nullable|string|max:255',

            // file upload
            'file_sttb' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'akta_kelahiran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'kk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pas_foto' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $server_key = env('MIDTRANS_SERVER_KEY');
        $orderId = $validatedData['order_id'];

        $response = Http::withBasicAuth($server_key, '')
            ->get("https://api.sandbox.midtrans.com/v2/{$orderId}/status");

        if (!$response->successful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memverifikasi status transaksi dari Midtrans.',
            ], 500);
        }

        $midtransStatus = $response->json();

        $transactionStatus = $midtransStatus['transaction_status'] ?? 'unknown';

        $uploadedFiles = [];
        $fileFields = ['file_sttb', 'akta_kelahiran', 'kk', 'pas_foto'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = ucfirst($field) . '_' . Str::slug($validatedData['nama']) . '_' . date('YmdHis') . '.' . $ext;
                $destination = public_path("storage/files/spmb/$field/");

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    // Contoh helper resize, sesuaikan dengan implementasi kamu
                    ImageHelper::resizeAndSave($file, $destination, $filename);
                } else {
                    $file->move($destination, $filename);
                }

                $uploadedFiles[$field] = $filename;
            } else {
                $uploadedFiles[$field] = null;
            }
        }

        $phone = $request->phone_orang_tua ?? $request->phone_wali ?? null;

        if(Spmb::where('order_id', $orderId)->exists()) {
            $spmb = Spmb::where('order_id', $orderId)->first();
            $spmb->update([
                'phone' => $phone,
                'file_sttb' => $uploadedFiles['file_sttb'],
                'akta_kelahiran' => $uploadedFiles['akta_kelahiran'],
                'kk' => $uploadedFiles['kk'],
                'pas_foto' => $uploadedFiles['pas_foto'],
            ]);
        }

        // Buat data SPMB
        $spmb = Spmb::updateOrCreate(
            [
                'order_id' => $validatedData['order_id'],
            ],
            [
                // 'order_id' => $validatedData['order_id'],
                'nama' => $validatedData['nama'],
                'tempat_lahir' => $validatedData['tempat_lahir'],
                'tanggal_lahir' => $validatedData['tanggal_lahir'],
                'jenis_kelamin' => $validatedData['jenis_kelamin'],
                'agama' => $validatedData['agama'],
                'suku' => $validatedData['suku'],
                'alamat' => $validatedData['alamat'],
                'nama_asal_sekolah' => $validatedData['nama_asal_sekolah'] ?? null,
                'sttb' => $validatedData['sttb'] ?? null,
                'alamat_sekolah' => $validatedData['alamat_sekolah'] ?? null,
                'select_data' => $validatedData['select_data'],
                'nama_ayah' => $validatedData['nama_ayah'] ?? null,
                'nama_ibu' => $validatedData['nama_ibu'] ?? null,
                'alamat_ayah' => $validatedData['alamat_ayah'] ?? null,
                'alamat_ibu' => $validatedData['alamat_ibu'] ?? null,
                'pendidikan_ayah' => $validatedData['pendidikan_ayah'] ?? null,
                'pendidikan_ibu' => $validatedData['pendidikan_ibu'] ?? null,
                'pekerjaan_ayah' => $validatedData['pekerjaan_ayah'] ?? null,
                'pekerjaan_ibu' => $validatedData['pekerjaan_ibu'] ?? null,
                'nama_wali' => $validatedData['nama_wali'] ?? null,
                'pekerjaan_wali' => $validatedData['pekerjaan_wali'] ?? null,
                'alamat_wali' => $validatedData['alamat_wali'] ?? null,
                'phone' => $phone,
                'file_sttb' => $uploadedFiles['file_sttb'],
                'akta_kelahiran' => $uploadedFiles['akta_kelahiran'],
                'kk' => $uploadedFiles['kk'],
                'pas_foto' => $uploadedFiles['pas_foto'],
                'nomor_urut' => Spmb::max('nomor_urut') + 1,
                'status_pembayaran' => $transactionStatus,
            ]
        );

        // Hapus order_id dari session agar tidak dipakai lagi
        $request->session()->forget('spmb_order_id');

        return redirect()->route('spmb.success', $orderId)->with('success', 'Pendaftaran Berhasil!');

    }

    public function success($orderID)
    {
        $spmb = Spmb::where('order_id', $orderID)->firstOrFail();
        return view('spmb.success', compact('spmb'));
    }

    public function formDetail($orderId)
    {
        $spmb = Spmb::where('order_id', $orderId)->firstOrFail();

        if($spmb->status_pembayaran != 'settlement') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran Belum Lunas'
            ]);
        }

        if(!$spmb) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Tidak Di Temukan'
            ]);
        }


        return view('spmb.form_detail', compact('spmb'));
    }

    public function formDetailStore(Request $request)
    {
        $request->validate([
            // ''
        ]);
    }
}
