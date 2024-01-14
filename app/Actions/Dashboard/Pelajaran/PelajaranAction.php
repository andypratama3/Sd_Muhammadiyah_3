<?php

namespace App\Actions\Dashboard\Pelajaran;

use App\Models\Pelajaran;

class PelajaranAction {

    public function execute($pelajaranData)
    {
        $matapelajaran = Pelajaran::updateOrCreate(
            ['slug' => $pelajaranData->slug],
            [
                'name' => $pelajaranData->name,
            ]
        );
    }

}
