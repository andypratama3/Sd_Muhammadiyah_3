<?php

namespace App\Actions\Dashboard\Kelas;

use App\Models\Kelas;

class KelasAction {

    public function execute($kelasData)
    {
        $category_kelas = json_encode($kelasData->category_kelas);
        $kelas = Kelas::updateOrCreate(
            ['slug' => $kelasData->slug],
            [
                'name' => $kelasData->name,
                'category_kelas' => $category_kelas,
            ]
        );
    }

}
