<?php

namespace App\Actions\Dashboard\TenagaPendidikan;

use App\Models\TenagaPendidikan;

class TenagaPendidikanActionDelete
{
    public function execute($id)
    {
        $tenagaPendidikan = TenagaPendidikan::where('slug', $id)->firstOrFail();
        $tenagaPendidikan->delete();

        return $tenagaPendidikan;
    }
}
