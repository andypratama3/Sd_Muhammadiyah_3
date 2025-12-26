<?php

namespace App\Actions\Dashboard\Prestasi;

use App\Models\Prestasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class PrestasiAction
{
    public function execute($request)
    {
        // ambil data lama
        $prestasi = Prestasi::where('slug', $request->slug)->first();

        $oldFoto = $prestasi?->foto;
        $oldDesc = $prestasi?->description;

        // ===== FOTO =====
        if ($request->foto) {
            $file = $request->foto;
            $ext = $file->getClientOriginalExtension();

            $picture_name = 'Prestasi_' .
                Str::slug($request->name) . '_' .
                date('YmdHis') . '.' . $ext;

            $upload_path = public_path('storage/img/prestasi/');

            // Pastikan folder ada
            if (!File::exists($upload_path)) {
                File::makeDirectory($upload_path, 0755, true);
            }

            $file->move($upload_path, $picture_name);

            // Hapus foto lama jika ada
            if ($oldFoto && $oldFoto !== $picture_name) {
                $oldFilePath = $upload_path . $oldFoto;
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }
            }
        } else {
            $picture_name = $oldFoto;
        }

        // Generate slug jika create baru
        $slug = $request->slug ?? Str::slug($request->name) . '-' . date('YmdHis');

        // ===== SIMPAN PRESTASI =====
        $prestasi = Prestasi::updateOrCreate(
            [
                'slug' => $slug,
            ],
            [
                'name'        => $request->name,
                'description' => $request->description ?? $oldDesc,
                'foto'        => $picture_name,
                'tingkat'     => $request->tingkat,
                'penyelenggara' => $request->penyelenggara,
                'tanggal'     => $request->tanggal,
                'juara'       => $request->juara,
                'status'      => $request->status,
            ]
        );

        // ===== SINKRONISASI KATEGORI =====
        if (!empty($request->prestasi_kategori)) {
            if (empty($request->slug)) {
                // Create baru
                $prestasi->prestasi_kategori()->attach($request->prestasi_kategori);
            } else {
                // Update
                $prestasi->prestasi_kategori()->sync($request->prestasi_kategori);
            }
        }

        return $prestasi;
    }
}
