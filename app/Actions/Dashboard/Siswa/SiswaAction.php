<?php

namespace App\Actions\Dashboard\Siswa;

use App\Models\Siswa;
use App\Models\Pelajaran;
use Illuminate\Support\Str;

class SiswaAction {

    public function execute($siswaData)
    {
        if($siswaData->foto){
            $foto = $siswaData->foto;
            $ext = $foto->getClientOriginalExtension();
            //upload foto to folder
            $upload_path = public_path('storage/img/siswa/');
            $picture_name = 'Siswa_'.Str::slug($siswaData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);
        }else {
            $picture_name = Str::slug($siswaData->name).'jpg';
        }
        // dd($siswaData->nik);
        $siswa = Siswa::updateOrCreate(
            ['slug' => $siswaData->slug],
            [
                'name' => $siswaData->name,
                'jk' => $siswaData->jk,
                'tmpt_lahir' => $siswaData->tmpt_lahir,
                'tgl_lahir' => $siswaData->tgl_lahir,
                'nik' => $siswaData->nik,
                'nisn' => $siswaData->nisn,
                'agama' => $siswaData->agama,
                'rt' => $siswaData->rt,
                'rw' => $siswaData->rw,
                'provinsi_id' => $siswaData->provinsi_id,
                'kabupaten_id' => $siswaData->kabupaten_id,
                'kecamatan_id' => $siswaData->kecamatan_id,
                'kelurahan_id' => $siswaData->kelurahan_id,
                'nama_jalan' => $siswaData->nama_jalan,
                'jenis_tinggal' => $siswaData->jenis_tinggal,
                'no_hp' => $siswaData->no_hp,
                'beasiswa' => $siswaData->beasiswa,
                'foto' => $picture_name,
            ]
        );
        if(empty($siswaData->slug)){
            // $siswa->kelas()->attach($siswaData->kelas);
            $siswa->kelas()->attach([$siswaData->kelas => ['category_kelas' => $siswaData->category_kelas]]);
        }else{
            // $siswa->kelas()->sync($siswaData->kelas);
            $siswa->kelas()->sync([$siswaData->kelas => ['category_kelas' => $siswaData->category_kelas]]);

        }

    }

}
