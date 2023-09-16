<?php
namespace App\Actions\Dashboard\Artikel;
use App\Actions\Dashboard\Artikel\ArtikelDeleteAction;
class ArtikelDeleteAction
{
    public function execute($artikel)
    {
        $artikel->delete();
    }
}
