<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use Illuminate\Support\Str;

use App\Models\Esktrakurikuler;

class ActionEkstrakurikuler
{
    public function execute($ekstrakurikulerData)
    {
        if ($ekstrakurikulerData->foto) {
            $images_ekstrakurikuler = $ekstrakurikulerData->foto;
            $images = [];

            foreach ($images_ekstrakurikuler as $img) {
                $ext = $img->getClientOriginalExtension();
                $upload_path = public_path('storage/ekstrakurikuler/');
                $uniqueIdentifier = Str::random(8);
                $file_name = 'E_kurikuler' . Str::slug($ekstrakurikulerData->name) . '_' . $uniqueIdentifier . '_' . date('YmdHis') . ".$ext";
                $img->move($upload_path, $file_name);
                $images[] = $file_name;
            }
        }


        $ekstrakurikuler = Esktrakurikuler::updateOrCreate(
            ['slug' => $ekstrakurikulerData->slug],
            [
                'name' => $ekstrakurikulerData->name,
                'desc' => $ekstrakurikulerData->desc,
                'foto' => implode(',', $images),
            ]
        );
        return $ekstrakurikuler;
    }

}
