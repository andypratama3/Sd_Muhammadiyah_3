<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpmbController extends Controller
{
    // public function __construct($midatransPayment)
    // {
    //     $this->midatransPaymentCallback = $midatransPayment;
    // }

    public function index()
    {
        // return view('spmb.index');
        return view('spmb.comming_soon');
    }

    public function store(Request $request)
    {
        $request->validate([
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
            'pendidikan_ibu' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',

            // wali
            'nama_wali' => 'nullable|string|max:100',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'alamat_wali' => 'nullable|string|max:255',

            // file upload (wajib/opsional)
            'file_sttb' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'akta_kelahiran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'kk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pas_foto' => 'required|file|mimes:jpg,jpeg,png|max:2048',

            'status_pembayaran' => 'required|string|max:100',
        ]);

        $uploadedFiles = [];
        $fileFields = ['file_sttb', 'akta_kelahiran', 'kk', 'pas_foto'];

        foreach ($fileFields as $field) {
            $file = $request->file($field);

            if ($file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $filename = ucfirst($field) . '_' . Str::slug($request->nama) . '_' . date('YmdHis') . '.' . $ext;
                $destination = public_path("storage/files/spmb/$field/");

                // Buat folder jika belum ada
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    ImageHelper::resizeAndSave($file, $destination, $filename);
                } else {
                    $file->move($destination, $filename);
                }

                $uploadedFiles[$field] = $filename;
            } else {
                $uploadedFiles[$field] = null;
            }
        }

        $spmb = Spmb::create([
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'agama' => $request->agama,
            'suku' => $request->suku,
            'alamat' => $request->alamat,
            'nama_asal_sekolah' => $request->nama_asal_sekolah,
            'sttb' => $request->sttb,
            'alamat_sekolah' => $request->alamat_sekolah,
            'select_data' => $request->select_data,

            'nama_ayah' => $request->nama_ayah,
            'nama_ibu' => $request->nama_ibu,
            'pendidikan_ayah' => $request->pendidikan_ayah,
            'pendidikan_ibu' => $request->pendidikan_ibu,
            'pekerjaan_ayah' => $request->pekerjaan_ayah,
            'pekerjaan_ibu' => $request->pekerjaan_ibu,
            'nama_wali' => $request->nama_wali,
            'pekerjaan_wali' => $request->pekerjaan_wali,
            'alamat_wali' => $request->alamat_wali,

            'file_sttb' => $uploadedFiles['file_sttb'],
            'akta_kelahiran' => $uploadedFiles['akta_kelahiran'],
            'kk' => $uploadedFiles['kk'],
            'pas_foto' => $uploadedFiles['pas_foto'],
            'status_pembayaran' => $request->status_pembayaran,
        ]);

        return response()->json([
            'status' => $spmb ? 'success' : 'error',
            'data' => $spmb,
            'message' => $spmb ? 'Data berhasil disimpan.' : 'Gagal menyimpan data.'
        ]);
    }

    public function pay(Reques $request)
    {
        // spmb pay 300.000
        return response()->json([
            'status' => 'success',
            'data' => 'data',
        ]);
    }

    private function payment()
    {

    }
}
