<?php

namespace App\Actions\Dashboard\Guru;

use App\Models\Guru;
use App\Models\Karyawan;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use App\DataTransferObjects\GuruData;
use Illuminate\Support\Facades\Storage;

class GuruAction
{
    public function execute(GuruData $guruData)
    {
        $picture_name = null;
        $karyawan = null;

        // search karyawan_id
        if($guruData->karyawan_id) {
            $karyawan = Karyawan::where('id', $guruData->karyawan_id)->first();
        }

        // request foto
        if($guruData->foto)
        {
            $foto = $guruData->foto;
            $ext = $foto->getClientOriginalExtension();
            $nameSlug = $karyawan ? Str::slug($karyawan->name) : Str::slug($guruData->name);

            // Delete old foto jika update
            if(!empty($guruData->slug)) {
                $oldGuru = Guru::where('slug', $guruData->slug)->first();
                if($oldGuru && $oldGuru->foto) {
                    Storage::disk('public')->delete('img/guru/' . $oldGuru->foto);
                }
            }

            // upload foto to folder
            $upload_path = public_path('storage/img/guru/');
            $picture_name = 'Guru_'.$nameSlug.'_'.date('YmdHis').".$ext";
            ImageHelper::resizeAndSave($foto, $upload_path, $picture_name);

        } else {
            // Jika tidak ada foto baru, gunakan foto lama saat update
            if(!empty($guruData->slug)) {
                $oldGuru = Guru::where('slug', $guruData->slug)->first();
                $picture_name = $oldGuru ? $oldGuru->foto : null;
            }
        }

        $slug = !empty($guruData->slug) ? $guruData->slug : Str::slug($guruData->name);

        $guru = Guru::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $karyawan->name ?? $guruData->name,
                'description' => $guruData->description,
                'lulusan' => $guruData->lulusan,
                'karyawan_id' => $guruData->karyawan_id,
                'foto' => $picture_name,
            ]
        );

        if(empty($guruData->slug))
        {
            $guru->pelajarans()->attach($guruData->pelajarans);
        }else{
            $guru->pelajarans()->sync($guruData->pelajarans);
        }

        return $guru;
    }
}
