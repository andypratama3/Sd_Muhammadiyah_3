<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use Illuminate\Support\Str;

use App\Models\Esktrakurikuler;

class ActionEkstrakurikuler
{
    public function execute($ekstrakurikulerData)
    {
        //request foto
        $foto = $ekstrakurikulerData->foto;
        $ext = $foto->getClientOriginalExtension();

        //upload foto to folder
        $upload_path = public_path('storage/img/ekstrakurikuler/');
        $picture_name = 'Berita_'.Str::slug($ekstrakurikulerData->name).'_'.date('YmdHis').".$ext";
        $foto->move($upload_path, $picture_name);

        $ekstrakurikuler = Esktrakurikuler::updateOrCreate(
            ['slug' => $ekstrakurikulerData->slug],
            [
                'name' => $ekstrakurikulerData->name,
                'desc' => $ekstrakurikulerData->desc,
                'foto' => $picture_name,
            ]
        );

        return $ekstrakurikuler;
    }

}
