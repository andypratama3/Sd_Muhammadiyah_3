<?php

namespace App\Actions\Dashboard\TenagaPendidikan;

class TenagaPendidikanActionDelete
{
    public function execute($tenagaPendidikan)
    {
        $tenagaPendidikan->delete();
    }
}
