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
            return redirect()->route('kontak.index')->with('success', 'Kritik dan Saran Berhasil Di Kirim');
        }else{
            return redirect()->route('kontak.index')->with('error', 'Kritik dan Saran Gagal Di Kirim');

        }

    }
}
