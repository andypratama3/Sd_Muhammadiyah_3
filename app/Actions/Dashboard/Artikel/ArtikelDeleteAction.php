<?php
namespace App\Actions\Dashboard\Artikel;

use App\Models\Artikel;

class ArtikelDeleteAction
{
    public function execute($slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();
        $artikel->delete();
    }
}
