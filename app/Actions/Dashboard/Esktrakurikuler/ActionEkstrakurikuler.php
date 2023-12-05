<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use Illuminate\Support\Str;

use App\Models\Esktrakurikuler;

class ActionEkstrakurikuler
{
    public function execute($ekstrakurikulerData)
    {
        //request foto


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
