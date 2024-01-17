<?php

namespace App\Actions\Dashboard\Prestasi;

use App\Models\Prestasi;

class PrestasiActionDelete
{
    public function execute($slug)
    {
        $prestasi = Prestasi::where('slug', $slug)->firstOrFail();
        $prestasi->delete();
    }
}
