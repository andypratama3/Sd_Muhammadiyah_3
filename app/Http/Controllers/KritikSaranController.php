<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTransferObjects\KritikSaranData;
use App\Actions\KritikSaranAction;
class KritikSaranController extends Controller
{
    public function store(KritikSaranData $kritikSaranData, KritikSaranAction $kritikSaranAction)
    {
        if($kritikSaranAction)
        {
            $kritikSaranAction->execute($kritikSaranData);
            return response()->json(['status' => 'success', 'message' => 'Kritik dan Saran Berhasil Di Kirim']);
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Artikel']);
        }

    }
}
