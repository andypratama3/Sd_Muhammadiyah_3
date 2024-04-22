<?php
namespace App\Actions\Dashboard\KritikSaran;

use App\Models\KritikSaran;
class KritikSaranActionDelete
{
    public function execute($slug)
    {
        $kritikSaran = KritikSaran::where('slug', $slug)->firstOrFail();
        $kritikSaran->delete();
        return $kritikSaran;
    }
}
