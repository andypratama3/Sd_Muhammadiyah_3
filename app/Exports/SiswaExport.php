<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Illuminate\Contracts\View\View;

class SiswaExport implements FromView,WithHeadings
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
        $siswas->transform(function ($siswa) {
            $siswa->umur = now()->diffInYears($siswa->tgl_lahir);
            return $siswa;
        });
        return view('dashboard.data.siswa.excel', [
            'siswas' => $siswas,
        ]);
    }
    public function headings(): array
    {
        return [
                "Nama",
                "Jenis Kelamin",
                "Tempat Lahir",
                "Tanggal Lahir",
                "Nisn",
                "Agama",
                // data sekolah
                "Kelas/tahun",
                "Tanggal Masuk",
                "Beasiswa",
                // data orang tua
                "Nama Ayah",
                "Nama Ibu",
                "Pendidikan Ayah",
                "Pendidikan Ibu",
                //pekerjaan
                "Pekerjaan_Ayah",
                "Pekerjaan Ibu",
                //wali
                "Nama wali",
                "Pekerjaan wali",
                "Alamat wali",
                //alamat
                "Rt",
                "Rw",
                "provinsi",
                "kabupaten",
                "kecamatan",
                "kelurahan",
                "Nama Jalan",
                "Jenis Tinggal",
                "No HP",
        ];
    }
}
