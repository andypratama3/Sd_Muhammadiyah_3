<?php
namespace App\Actions\Dashboard\Jadwal;

use App\Models\Jadwal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class JadwalAction
{
    public function execute($jadwalData)
    {
        $file = $jadwalData->jadwal;
        $ext = $file->getClientOriginalExtension();

        //upload foto to folder
        $upload_path = public_path('storage/file/jadwal/');
        $picture_name = 'Jadwal_'.Str::slug($jadwalData->kelas).'_'.date('YmdHis').".$ext";
        $file->move($upload_path, $picture_name);

        $jadwal = Jadwal::updateOrCreate(
            [ 'slug' => $jadwalData->slug ],
            [
                'smester' => $jadwalData->smester,
                'jadwal' => $jadwalData->slug,
                'kelas' => $jadwalData->kelas,
                'jadwal' => $picture_name,
                'category_kelas' => $jadwalData->category_kelas,
            ]

            );
        return $jadwal;
    }
}
