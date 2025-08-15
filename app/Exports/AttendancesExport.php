<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Attendances;
use Maatwebsite\Excel\Concerns\FromCollection;

class AttendancesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }


    public function collection()
    {
        $kelasLulus = Kelas::where('name', 'Lulus')->first();
        $siswas = Siswa::whereHas('kelas', function ($query) {
            $query->where('name', '!=', 'Lulus');
        });

        $attendances = Attendances::select(['siswa_id','kelas_id'])
                        ->groupBy('siswa_id')
                        ->where('tanggal', $this->date)
                        ->get();


        dd($attendances);

    }
}
