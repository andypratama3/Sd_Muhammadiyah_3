<?php

namespace App\Actions\Dashboard\Prestasi;

use App\Models\Prestasi;
use Illuminate\Support\Str;

class PrestasiAction {

    public function execute($PrestasiData)
    {
        if($PrestasiData->foto){
            $file = $PrestasiData->foto;
            $ext = $file->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/prestasi/');
            $picture_name = 'Prestasi_'.Str::slug($PrestasiData->name).'_'.date('YmdHis').".$ext";
            $file->move($upload_path, $picture_name);
        }
        $prestasi = Prestasi::updateOrCreate(
            ['slug' => $PrestasiData->slug],
            [
                'name' => $PrestasiData->name,
                'description' => $PrestasiData->description,
                'foto' => $picture_name,
                'status' => $PrestasiData->status,
            ]
        );
    }

}
