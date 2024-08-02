<?php

namespace App\Actions\Dashboard\Gallery;

use App\Models\Gallery;
use Illuminate\Support\Str;

class GalleryAction
{
    public function execute($galleryData)
    {
        //request foto
        if($galleryData->foto){
            $foto = $galleryData->foto;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/gallery/');
            $picture_name = 'Gallery_'.Str::slug($galleryData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);
        }else{
            $gallery = Gallery::where('slug', $galleryData->slug)->first();
            $picture_name = $gallery->foto;
        }

        $gallery = Gallery::updateOrCreate(
            ['slug' => $galleryData->slug],
            [
                'name' => $galleryData->name,
                'foto' => $picture_name,
            ]
        );

        return $gallery;
    }

}
