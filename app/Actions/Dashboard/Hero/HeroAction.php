<?php

namespace App\Actions\Dashboard\Hero;

use App\Models\Hero;
use App\Models\Artikel;
use Illuminate\Support\Str;


class HeroAction
{
    public function execute($heroData)
    {
        if($heroData->image) {
            //request foto
            $foto = $heroData->image;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/hero/');
            $picture_name = 'Hero_'.Str::slug($heroData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);
        }else{
            $hero = Hero::where('slug', $heroData->slug)->first();
            $picture_name = $hero->image;
        }

        $hero = Hero::updateOrCreate(
            ['slug' => $heroData->slug],
            [
                'name' => $heroData->name,
                'desc' => $heroData->desc,
                'image' => $picture_name,
                'youtube' => $heroData->youtube,
                'link' => $heroData->link,
            ]);

        return $hero;
    }
}
