<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use Illuminate\Support\Str;

use App\Models\Esktrakurikuler;

class ActionEkstrakurikuler
{
    public function execute($ekstrakurikulerData)
    {
        // Handle foto images with array data
        if ($ekstrakurikulerData->foto) {
            $ekstrakurikuler_files = $ekstrakurikulerData->foto;
            $ekstrakurikuler_name = [];

            foreach ($ekstrakurikuler_files as $esktrakurikuller_file) {
                $ext = $esktrakurikuller_file->getClientOriginalExtension();
                $upload_path = public_path('storage/ekstrakurikuler');
                $file_name_cover = 'Ekstrakurikuller_' . Str::slug($ekstrakurikulerData->name) . '_' . date('YmdHis') . ".$ext";
                $esktrakurikuller_file->move($upload_path, $file_name_cover);
                $ekstrakurikuler_name[] = $file_name_cover;
            }
        }

        $ekstrakurikuler = Esktrakurikuler::updateOrCreate(
            ['slug' => $ekstrakurikulerData->slug],
            [
                'name' => $ekstrakurikulerData->name,
                'desc' => $ekstrakurikulerData->desc,
                'foto' => implode(',', $ekstrakurikuler_name),
            ]
        );
        return $ekstrakurikuler;
    }

}
