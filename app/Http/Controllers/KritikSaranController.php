<?php

namespace App\Http\Controllers;

use App\Actions\KritikSaranAction;
use App\DataTransferObjects\KritikSaranData;

class KritikSaranController extends Controller
{
    public function store(KritikSaranData $kritikSaranData, KritikSaranAction $kritikSaranAction)
    {
        if ($kritikSaranAction) {
            $kritikSaranAction->execute($kritikSaranData);

            return redirect()->route('kontak.success');
        } else {
            return redirect()->route('kontak.index')->with('error', 'Kritik dan Saran Gagal Di Kirim');

        }
    }
}
