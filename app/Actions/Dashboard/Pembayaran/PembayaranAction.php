<?php

namespace App\Actions\Dashboard\Pembayaran;

use App\Models\Pembayaran;
use App\Http\Controllers\Api\Dashboard\SendOrderIDWhatsAppApi;

class PembayaranAction
{
    protected $whatsApp;

    public function __construct(SendOrderIDWhatsAppApi $whatsApp)
    {
        $this->whatsApp = $whatsApp; // Corrected assignment here
    }

    public function execute($pembayaranData)
    {
        $grossAmount = $pembayaranData->gross_amount;
        $grossAmount = str_replace('.', '', $grossAmount);

        if(empty($pembayaranData->order_id)){
            $order_id = rand();
        }else{
            $order_id = $pembayaranData->order_id;
        }

        // Update or create payment record
        $pembayaran = Pembayaran::updateOrCreate(
            ['order_id' => $pembayaranData->order_id],
            [
                'order_id' => $order_id,
                'siswa_id' => $pembayaranData->siswa_id,
                'kelas_id' => $pembayaranData->kelas_id,
                'category_kelas' => $pembayaranData->category_kelas,
                'judul_id' => $pembayaranData->judul_id,
                'gross_amount' => $grossAmount,
            ]
        );
        // $this->whatsApp->sendMessage($pembayaran->order_id);
    }
}
