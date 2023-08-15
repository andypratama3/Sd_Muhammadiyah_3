<?php

namespace App\Actions\Dashboard\Berita;

use App\Models\Berita;
use Illuminate\Support\Str;

class ActionBerita
{
    public function execute($beritaData)
    {

        $foto = $beritaData->foto;
        $imageFile = 'Berita_'.Str::slug($beritaData->judul). $foto->getClientOriginalName();
        $foto->storeAs('berita', $imageFile, 'public');

        $berita = Berita::updateOrCreate(
            ['slug' => $beritaData->slug],
            [
                'judul' => $beritaData->judul,
                'desc' => $beritaData->desc,
                'foto' => $imageFile,
            ]
        );

        return $berita;
    }
}
