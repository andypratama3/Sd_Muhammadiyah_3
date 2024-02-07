<?php
namespace App\Actions\Dashboard\Pembayaran;

use App\Models\Pembayaran;


class PembayaranActionDelete
{
    public function execute($order_id)
    {
        $pembayaran = Pembayaran::where('order_id', $order_id)->firstOrFail();
        $pembayaran->delete();
    }
}

