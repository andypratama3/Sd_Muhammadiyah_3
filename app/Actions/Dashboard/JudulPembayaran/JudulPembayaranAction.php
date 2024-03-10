<?php
namespace App\Actions\Dashboard\JudulPembayaran;

use App\Models\JudulPembayaran;


class JudulPembayaranAction
{
    public function execute($judulPembayaranData)
    {

        $kategori = JudulPembayaran::updateOrCreate(
            [ 'slug' => $judulPembayaranData->slug ],
            [
                'name' => $judulPembayaranData->name,
            ]
            );
        return $kategori;
    }
}
