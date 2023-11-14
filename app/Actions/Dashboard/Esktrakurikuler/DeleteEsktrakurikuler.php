<?php

namespace App\Actions\Dashboard\Esktrakurikuler;

use App\Models\Esktrakurikuler;

class DeleteEsktrakurikuler
{
    public function execute($slug): void
    {
        $esktrakurikuler = Esktrakurikuler::where('slug', $slug)->firstOrFail();
        $esktrakurikuler->delete();
    }
}
