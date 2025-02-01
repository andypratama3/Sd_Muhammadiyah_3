<?php

namespace App\Actions\Dashboard\Artikel;

use App\Models\Artikel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\DataTransferObjects\ArtikelData;

class ArtikelAction
{
    public function execute(ArtikelData $artikelData)
    {
        if($artikelData->image) {
            //request foto
            $foto = $artikelData->image;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/artikel/');
            $picture_name = 'Artikel_'.Str::slug($artikelData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);
        }else{
            $artikel = Artikel::where('slug', $artikelData->slug)->first();
            $picture_name = $artikel->image;

        }

        $artikel = Artikel::updateOrCreate(
            ['slug' => $artikelData->slug],
            [
                'name' => $artikelData->name,
                'artikel' => $artikelData->artikel,
                'image' => $picture_name,
                'user_id' => Auth::id(),
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
