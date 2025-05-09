<?php

namespace App\Actions\Dashboard\Gallery;

use App\Models\Gallery;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;

class GalleryAction
{
    public function execute($galleryData)
    {
        $fotorFiles = $galleryData->foto;
        $galleryName = [];

        // Ambil galeri lama (jika ada)
        $existingGallery = Gallery::where('slug', $galleryData->slug)->first();

        if ($existingGallery) {
            $oldFotos = explode(',', $existingGallery->foto);

            // Hapus semua foto lama dari storage
            foreach ($oldFotos as $oldFoto) {
                $filePath = 'public/img/gallery/' . trim($oldFoto);
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }
        }

        $galleryCover = null;

        if($galleryData->cover) {
            $foto = $galleryData->cover;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/gallery/cover/');
            $galleryCover = 'GalleryCover_'.Str::slug($galleryData->name).'_'.date('YmdHis').".$ext";
            // $foto->move($upload_path, $galleryCover);
            ImageHelper::resizeAndSave($foto, $upload_path, $galleryCover);
        } else {
            $galleryCover = Gallery::where('slug', $galleryData->slug)->first()->cover;
        }
        // Upload foto baru
        foreach ($fotorFiles as $fotorFile) {
            $ext = $fotorFile->getClientOriginalExtension();
            $uniqueIdentifier = Str::random(8);
            $file_name = 'Gallery_' . Str::slug($galleryData->name) . '_' . $uniqueIdentifier . '_' . date('YmdHis') . ".$ext";
            $upload_path = public_path('storage/img/gallery/');
            ImageHelper::resizeAndSave($fotorFile, $upload_path, $file_name);

            $galleryName[] = $file_name;
        }

        // Simpan galeri
        $gallery = Gallery::updateOrCreate(
            ['slug' => $galleryData->slug],
            [
                'name' => $galleryData->name,
                'cover' => $galleryCover,
                'foto' => implode(',', $galleryName),
            ]
        );

        return $gallery;
    }
}
