<?php

namespace App\Actions\Dashboard\Siswa;

use App\Models\Pelajaran;

class SiswaAction {

    public function execute($siswaData)
    {
        if($siswaData->foto){
        $ext = $foto->getClientOriginalExtension();
        //upload foto to folder
        $upload_path = public_path('storage/img/siswa/');
        $picture_name = 'Siswa_'.Str::slug($siswaData->name).'_'.date('YmdHis').".$ext";
        $foto->move($upload_path, $picture_name);
        }
        $siswa = Siswa::updateOrCreate(
            ['slug' => $siswaData->slug],
            [
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'name' => $siswaData->name,
                'foto' => $picture_name,
                'name' => $siswaData->name,
            ]
        );
    }

}
