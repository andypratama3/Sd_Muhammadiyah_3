<?php

namespace App\Actions\Dashboard\Guru;

use App\Models\Guru;
use App\Models\Karyawan;
use Illuminate\Support\Str;
use App\DataTransferObjects\GuruData;

class GuruAction
{
    public function execute(GuruData $guruData)
    {

        $picture_name = null;
        // search karyawan_id
        $karyawan = Karyawan::where('id', $guruData->karyawan_id)->firstOrFail();
        //request foto
        if($guruData->foto)
        {
            $foto = $guruData->foto;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/guru/');
            $picture_name = 'Guru_'.Str::slug($karyawan->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);

        }  else {
            $picture_name = $guruData->foto;
        }


        $guru = Guru::updateOrCreate(
            ['slug' => $guruData->slug],
            [
                'name' => $karyawan->name,
                'description' => $guruData->description,
                'lulusan' => $guruData->lulusan,
                'karyawan_id' => $guruData->karyawan_id,
                'foto' => $picture_name,
            ]);

        if(empty($guruData->slug))
        {
            $guru->pelajarans()->attach($guruData->pelajarans);
        }else{
            $guru->pelajarans()->sync($guruData->pelajarans);
        }
        return $guru;
    }
}
