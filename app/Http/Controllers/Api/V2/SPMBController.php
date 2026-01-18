<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Spmb;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;

class SPMBController extends Controller
{

    public function store(Request $request)
    {
        //

        try {
            $validated = $request->validate([
                // Data Pribadi
                'nama' => 'required|string|max:255',
                'nik' => 'nullable|string|size:16',
                'tempat_lahir' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'suku' => 'required|string|max:255',
                'jenis_kelamin' => 'required|string|in:laki-laki,perempuan',
                'asal_tk' => 'nullable|string|max:255',
                'alamat' => 'required|string',

                // Data Orang Tua
                'nama_ayah' => 'required|string|max:255',
                'pekerjaan_ayah' => 'required|string|max:255',
                'nama_ibu' => 'required|string|max:255',
                'pekerjaan_ibu' => 'required|string|max:255',
                'no_hp' => 'required|string|regex:/^08[0-9]{8,11}$/',

                // File Uploads
                'akta_kelahiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'sttb' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'pas_foto' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'kartu_keluarga' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            // Handle file uploads
            $fileFields = ['akta_kelahiran', 'sttb', 'pas_foto', 'kartu_keluarga'];
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('storage/spmb'), $filename);
                    $validated[$field] = $filename;
                }
            }

            // Generate nomor urut
            $lastNumber = Spmb::max('nomor_urut') ?? 0;
            $validated['nomor_urut'] = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

            // Set default values
            $validated['status_pembayaran'] = 'pending';
            $validated['order_id'] = 'ORD-' . strtoupper(Str::random(10));


            if($this->payment()){
                $spmb = Spmb::create($validated);
                $validated['status_pembayaran'] = 'paid';
            }else{
                $validated['status_pembayaran'] = 'pending';
            }
            // Create record

            // Return success dengan data
            return $this->created($spmb, 'Pendaftaran berhasil');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError('Data gagal dimasukkan', $e->errors());
        } catch (\Exception $e) {
            return $this->serverError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public static function payment()
    {

    }
}
