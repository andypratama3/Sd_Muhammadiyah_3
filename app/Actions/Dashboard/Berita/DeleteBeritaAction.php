<?php

namespace App\Actions\Dashboard\Berita;

use App\Models\Berita;

class DeleteBeritaAction
{
    public function execute($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        $berita->delete();
    }
}
