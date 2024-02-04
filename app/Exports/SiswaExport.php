<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class SiswaExport implements FromCollection, FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {

    // }
    public function view(): view
    {
        $siswas = Siswa::all();
    }
}
