<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\FotoSekolah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TentangDataController extends Controller
{
    public function fotoSekolah()
    {
       try {
           $fotosekolah = FotoSekolah::orderBy('created_at', 'desc')->get();

           if($fotosekolah) {
               return $this->success($fotosekolah ?? [], 'Berhasil Menerima Data');
           }

           return $this->error('Data tidak ditemukan');
       } catch (\Throwable $e) {
          return $this->serverError('Gagal mengambil data: ' . $e->getMessage());
       }
    }
}
