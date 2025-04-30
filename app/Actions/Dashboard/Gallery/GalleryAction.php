<?php

namespace App\Actions\Dashboard\Gallery;

use App\Models\Gallery;
use Illuminate\Support\Str;

class GalleryAction
{
    public function execute($galleryData)
    {
        $fotorFiles = $galleryData->foto;
        $galleryName = [];

        foreach ($fotorFiles as $fotorFile) {
            $ext = $fotorFile->getClientOriginalExtension();
            $uniqueIdentifier = Str::random(8);
            $file_name = 'Gallery_' . Str::slug($galleryData->name) . '_' . $uniqueIdentifier . '_' . date('YmdHis') . ".$ext";
            $upload_path = public_path('storage/img/gallery/');
            $fotorFile->move($upload_path, $file_name);
            $galleryName[] = $file_name;
        } 

        $gallery = Gallery::updateOrCreate(
            ['slug' => $galleryData->slug],
            [
                'name' => $galleryData->name,
                'foto' =>  implode(',', $galleryName),
            ]
        );

        return $gallery;
    }

}
