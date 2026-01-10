<?php

namespace App\Actions\Dashboard\TenagaPendidikan;

use App\Models\TenagaPendidikan;

class TenagaPendidikanActionDelete
{
    public function execute($slug)
    {
        $tenagaPendidikan = TenagaPendidikan::where('slug', $slug)->firstOrFail();
        $tenagaPendidikan->delete();

        return $tenagaPendidikan;
    }
}
