<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Spmb;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SPMBController extends Controller
{
    public function store(Request $request)
    {

        $validated = $request->validate([
            'nama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string|in:Laki-Laki,Perempuan',
            'agama' => 'required|string',
            'suku' => 'required|string',
            'alamat' => 'required|string',
            'nama_asal_sekolah' => 'required|string',
            'sttb' => 'required|string',
            'alamat_sekolah' => 'required|string',
            'select_data' => 'required|string',
            'nama_ayah' => 'required|string',
            'nama_ibu' => 'required|string',
            'pendidikan_ayah' => 'required|string',
            'pendidikan_ibu' => 'required|string',
            'pekerjaan_ayah' => 'required|string',
            'pekerjaan_ibu' => 'required|string',
            'alamat_ayah' => 'required|string',
            'alamat_ibu' => 'required|string',
            'nama_wali' => 'required|string',
            'pekerjaan_wali' => 'required|string',
            'alamat_wali' => 'required|string',
            // 'file_sttb' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
            'file_sttb' => 'required',
            'akta_kelahiran' => 'required',
            'kk' => 'required',
            // 'order_id' => 'required|string',
            'pas_foto' => 'required',
            'phone' => 'required|string',
            'nomor_urut' => 'required|string',
            'status_pembayaran' => 'required|string',
        ]);

        $file_sttb_name = null;
        $akta_kelahiran_name = null;
        $kk_name = null;
        $pas_foto_name = null;

        // if($request->hasFile('file_sttb')) {
        //     $file_sttb = $request->file('file_sttb');
        //     $file_sttb_name = $file_sttb->getClientOriginalName();
        //     $file_sttb->move(public_path('file'), $file_sttb_name);
        //     $validated['file_sttb'] = $file_sttb_name;
        // }

        // if($request->hasFile('akta_kelahiran')) {
        //     $akta_kelahiran = $request->file('akta_kelahiran');
        //     $akta_kelahiran_name = $akta_kelahiran->getClientOriginalName();
        //     $akta_kelahiran->move(public_path('file'), $akta_kelahiran_name);
        //     $validated['akta_kelahiran'] = $akta_kelahiran_name;
        // }

        // if($request->hasFile('kk')) {
        //     $kk = $request->file('kk');
        //     $kk_name = $kk->getClientOriginalName();
        //     $kk->move(public_path('file'), $kk_name);
        //     $validated['kk'] = $kk_name;
        // }

        // if($request->hasFile('pas_foto')) {
        //     $pas_foto = $request->file('pas_foto');
        //     $pas_foto_name = $pas_foto->getClientOriginalName();
        //     $pas_foto->move(public_path('file'), $pas_foto_name);
        //     $validated['pas_foto'] = $pas_foto_name;
        // }

        // if($this->payment == true) {
        //     $spmb = Spmb::create($validated);

        //     return $this->success('success');
        // }

        // return $this->error('error');
    }

    private function payment()
    {
        // do payment
    }
}
