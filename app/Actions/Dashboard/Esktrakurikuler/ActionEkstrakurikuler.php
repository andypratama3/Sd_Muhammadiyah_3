<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Helpers\ImageHelper;
use App\Models\Esktrakurikuler;

class ActionEkstrakurikuler
{
    public function execute($ekstrakurikulerData)
    {
        $ekstrakurikulerOld = Esktrakurikuler::where('slug', $ekstrakurikulerData->slug)->first();

        // Ambil foto lama (jika ada)
        $images = $ekstrakurikulerOld?->foto
            ? explode(',', $ekstrakurikulerOld->foto)
            : [];

        // Jika upload foto baru
        if ($ekstrakurikulerData->foto) {
            $images = [];

            $upload_path = public_path('storage/img/ekstrakurikuler/');

            // Pastikan folder ada
            if (!File::exists($upload_path)) {
                File::makeDirectory($upload_path, 0755, true);
            }

            foreach ($ekstrakurikulerData->foto as $img) {
                $ext = $img->getClientOriginalExtension();
                $file_name = 'E_kurikuler_' .
                    Str::slug($ekstrakurikulerData->name) . '_' .
                    Str::random(8) . '_' .
                    now()->format('YmdHis') . '.' . $ext;

                ImageHelper::resizeAndSave($img, $upload_path, $file_name);
                $images[] = $file_name;
            }
        }

        return Esktrakurikuler::updateOrCreate(
            ['slug' => $ekstrakurikulerData->slug],
            [
                'name'     => $ekstrakurikulerData->name,
                'desc'     => $ekstrakurikulerData->desc,
                'kategori' => $ekstrakurikulerData->kategori,
                'jam'      => $ekstrakurikulerData->jam,
                'guru'     => $ekstrakurikulerData->guru,
                'kelas'    => $ekstrakurikulerData->kelas,
                'foto'     => !empty($images) ? implode(',', $images) : $ekstrakurikulerOld?->foto,
            ]
        );
    }
}
