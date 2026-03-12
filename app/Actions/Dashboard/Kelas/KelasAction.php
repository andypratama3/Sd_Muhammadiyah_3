<?php

namespace App\Actions\Dashboard\Kelas;

use App\Models\Kelas;

class KelasAction
{
    public function execute($kelasData): Kelas
    {
        $kelas = Kelas::updateOrCreate(
            ['slug' => $kelasData->slug],
            [
                'name'           => $kelasData->name,
                'category_kelas' => json_encode(array_values($kelasData->category_kelas)),
            ]
        );

        $kelas->refresh();

        // Filter nilai kosong
        $pelajaranIds = array_filter($kelasData->pelajaran_ids ?? $kelasData->pelajaran ?? [], fn($id) => !empty($id));

        if (!empty($pelajaranIds)) {
            if ($kelas->wasRecentlyCreated) {
                // Data baru → attach
                $kelas->kelasPelajaran()->attach($pelajaranIds);
            } else {
                // Data lama → sync (hapus yang tidak ada, tambah yang baru)
                $kelas->kelasPelajaran()->sync($pelajaranIds);
            }
        }

        return $kelas;
    }
}