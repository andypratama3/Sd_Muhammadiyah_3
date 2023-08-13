<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;

class DeleteFasilitasAction
{
    public function execute($slug): void
    {
        $berita = Fasilitas::where('slug', $slug)->firstOrFail();
        $berita->delete();
    }
}
