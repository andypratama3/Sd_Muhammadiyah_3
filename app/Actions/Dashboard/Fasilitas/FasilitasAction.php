<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use App\Models\KelengkapanFasilitas;

class FasilitasAction
{
    public function execute($FasilitasData)
    {
        // ambil fasilitas (WAJIB ADA)
        $fasilitas = Fasilitas::where('slug', $FasilitasData->slug)->firstOrFail();

        // foto lama
        $fotoLama = $fasilitas->foto;

        // upload foto baru (jika ada)
        if ($FasilitasData->foto) {
            $fotoBaru = [];

            foreach ($FasilitasData->foto as $file) {
                $ext = $file->getClientOriginalExtension();
                $fileName = 'Fasilitas_' .
                    Str::slug($FasilitasData->nama_fasilitas) . '_' .
                    Str::random(6) . '_' .
                    now()->format('YmdHis') . '.' . $ext;

                $path = public_path('storage/img/fasilitas/');
                ImageHelper::resizeAndSave($file, $path, $fileName);

                $fotoBaru[] = $fileName;
            }

            // gabungkan foto lama + baru (opsional)
            $fotoFinal = $fotoLama
                ? $fotoLama . ',' . implode(',', $fotoBaru)
                : implode(',', $fotoBaru);
        } else {
            $fotoFinal = $fotoLama;
        }

        // update fasilitas
        $fasilitas->update([
            'nama_fasilitas' => $FasilitasData->nama_fasilitas,
            'desc'           => $FasilitasData->desc,
            'ukuran'         => $FasilitasData->ukuran,
            'kapasitas'      => $FasilitasData->kapasitas,
            'foto'           => $fotoFinal,
        ]);

        // update kelengkapan
        if ($FasilitasData->kelengkapan) {
            // hapus lama
            KelengkapanFasilitas::where('fasilitas_id', $fasilitas->id)->delete();

            // simpan baru
            foreach ($FasilitasData->kelengkapan as $item) {
                if ($item) {
                    KelengkapanFasilitas::create([
                        'fasilitas_id' => $fasilitas->id,
                        'nama'         => $item,
                    ]);
                }
            }
        }

        return $fasilitas;
    }
}
