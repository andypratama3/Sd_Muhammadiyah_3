<?php

namespace App\Actions\Dashboard\Siswa;

use App\Models\Siswa;
use App\Models\Pelajaran;
use Illuminate\Support\Str;

class SiswaAction {

    public function execute($siswaData)
    {
        $siswa = Siswa::where('slug',$siswaData->slug)->first();

        /*
            ! change data compalate detail student
            ! masuk sekolah, nama orang tua , pekerjaan, alamat orang tua, wali
        */
        if(!$siswaData->foto){
                $picture_name = $siswa->foto;
        }else {
            $foto = $siswaData->foto;
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
                'jk' => $siswaData->jk,
                'tmpt_lahir' => $siswaData->tmpt_lahir,
                'tgl_lahir' => $siswaData->tgl_lahir,
                'nisn' => $siswaData->nisn,
                'agama' => $siswaData->agama,
                'nama_pendidikan' => $siswaData->nama_pendidikan,
                'nama_jalan_pendidikan' => $siswaData->nama_jalan_pendidikan,
                'kelas_tahun' => $siswaData->kelas_tahun,
                'tanggal_masuk' => $siswaData->tanggal_masuk,
                'beasiswa' => $siswaData->beasiswa,
                'foto' => $picture_name,
                'nama_ayah' => $siswaData->nama_ayah,
                'nama_ibu' => $siswaData->nama_ibu,
                'pendidikan_ayah' => $siswaData->pendidikan_ayah,
                'pendidikan_ibu' => $siswaData->pendidikan_ibu,
                'pekerjaan_ayah' => $siswaData->pekerjaan_ayah,
                'pekerjaan_ibu' => $siswaData->pekerjaan_ibu,
                'nama_wali' => $siswaData->nama_wali,
                'pekerjaan_wali' => $siswaData->pekerjaan_wali,
                'alamat_wali' => $siswaData->alamat_wali,
                'rt' => $siswaData->rt,
                'rw' => $siswaData->rw,
                'provinsi_id' => $siswaData->provinsi_id,
                'kabupaten_id' => $siswaData->kabupaten_id,
                'kecamatan_id' => $siswaData->kecamatan_id,
                'kelurahan_id' => $siswaData->kelurahan_id,
                'nama_jalan' => $siswaData->nama_jalan,
                'jenis_tinggal' => $siswaData->jenis_tinggal,
                'no_hp' => $siswaData->no_hp,
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
