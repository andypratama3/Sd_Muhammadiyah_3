<?php
namespace App\Actions\Dashboard\Pembayaran;


class PembayaranActionDelete
{
    public function execute($pembayaran)
    {
        $pembayaran->delete();
    }
}

