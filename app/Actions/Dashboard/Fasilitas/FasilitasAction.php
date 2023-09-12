<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FasilitasAction
{
    public function execute($FasilitasData)
    {
        $foto = $FasilitasData->foto;
        $ext = $foto->getClientOriginalExtension();

        $upload_path = 'storage/img/fasilitas/';
        $picture_name = 'Fasilitas_'.Str::slug($FasilitasData->nama_fasilitas).'_'.date('YmdHis').".$ext";
        $foto->move($upload_path, $picture_name);


        $fasilitas = Fasilitas::updateOrCreate(
            ['slug' => $FasilitasData->slug],
            [
                'nama_fasilitas' => $FasilitasData->nama_fasilitas,
                'desc' => $FasilitasData->desc,
                'foto' => $picture_name,
            ],
        );


        return $fasilitas;
    }

}

