<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FasilitasAction
{
    public function execute($FasilitasData)
    {
        $nama_fasilitas = $FasilitasData->nama_fasilitas;

        $fasilitas = Fasilitas::updateOrCreate(
            ['slug' => $FasilitasData->slug],
            [
                'nama_fasilitas' => $nama_fasilitas,
                'desc' => $FasilitasData->desc,
                'foto' => $FasilitasData->foto,
            ],
        );

        return $fasilitas;
    }

}
