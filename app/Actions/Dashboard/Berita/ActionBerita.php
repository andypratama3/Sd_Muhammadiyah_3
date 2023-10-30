<?php

namespace App\Actions\Dashboard\Berita;

use App\Models\Berita;
use Illuminate\Support\Str;

class ActionBerita
{
    public function execute($beritaData)
    {
        //request foto
        $foto = $beritaData->foto;
        $ext = $foto->getClientOriginalExtension();

        //upload foto to folder
        $upload_path = public_path('storage/img/berita/');
        $picture_name = 'Berita_'.Str::slug($beritaData->judul).'_'.date('YmdHis').".$ext";
        $foto->move($upload_path, $picture_name);

        $berita = Berita::updateOrCreate(
            ['slug' => $beritaData->slug],
            [
                'judul' => $beritaData->judul,
                'desc' => $beritaData->desc,
                'foto' => $picture_name,
            ]
        );

        return $berita;
    }

}
