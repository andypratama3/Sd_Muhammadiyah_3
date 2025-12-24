<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FasilitasDataController extends Controller
{
    public function fasilitasData()
    {
        $fasilitas = Fasilitas::with('kelengkapan')->orderBy('created_at', 'asc')->get();

        if($fasilitas){
            return $this->success($fasilitas, 'ok');
        }

        $this->error('Data Tidak Di Temukan');

    }

    public function show($id)
    {
        $fasilitas = Fasilitas::find($id);

        if($fasilitas){
            return $this->success($fasilitas, 'ok');
        }

        $this->error('Data Tidak Di Temukan');
    }
}
