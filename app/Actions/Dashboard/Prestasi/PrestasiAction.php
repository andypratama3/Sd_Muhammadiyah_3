<?php

namespace App\Actions\Dashboard\Prestasi;

use App\Models\Prestasi;
use Illuminate\Support\Str;

class PrestasiAction
{
    public function execute($request)
    {
        // ambil data lama
        $prestasi = Prestasi::where('slug', $request->slug)->firstOrFail();

        $oldFoto = $prestasi->foto;
        $oldDesc = $prestasi->description;

        // ===== FOTO =====
        if ($request->foto) {
            $file = $request->foto;
            $ext = $file->getClientOriginalExtension();

            $picture_name = 'Prestasi_' .
                Str::slug($request->name) . '_' .
                date('YmdHis') . '.' . $ext;

            $upload_path = public_path('storage/img/prestasi/');
            $file->move($upload_path, $picture_name);
        } else {
            $picture_name = $oldFoto;
        }

        // ===== UPDATE DATA =====
        $prestasi->update([
            'name'        => $request->name,
            'description' => $request->description ?? $oldDesc,
            'foto'        => $picture_name,
            'status'      => $request->status,
        ]);

        if ($request->prestasi_kategori) {
            $prestasi->prestasi_kategori()->sync($request->prestasi_kategori);
        }
    }
}
