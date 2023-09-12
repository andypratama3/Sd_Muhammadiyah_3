<?php

namespace App\Actions\Dashboard\Artikel;

use App\Models\Artikel;
use App\DataTransferObjects\ArtikelData;

class ArtikelAction
{
    public function execute(ArtikelData $artikelData)
    {
        $artikel = Artikel::updateOrCreate(
            ['slug' => $artikelData->slug],
            [
                'name' => $artikelData->name,
                'artikel' => $artikelData->artikel,
            ]);

        if(empty($artikelData->slug))
        {
            $artikel->categorys()->attach($artikelData->categorys);
        }else{
            $artikel->categorys()->sync($artikelData->categorys);
        }
        return $artikel;
    }
}
