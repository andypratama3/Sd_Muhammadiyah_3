<?php

namespace App\Exports;

use App\Exports\RekapitulasiSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapitulasiExport implements WithMultipleSheets
{
    protected $rekapitulasi;
    protected $bulan;

    public function __construct($rekapitulasi, $bulan)
    {
        $this->rekapitulasi = $rekapitulasi;
        $this->bulan = $bulan;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->rekapitulasi as $namaKelas => $dataSiswa) {
            $sheets[] = new RekapitulasiSheet($namaKelas, $dataSiswa, $this->bulan);
        }

        return $sheets;
    }
}
