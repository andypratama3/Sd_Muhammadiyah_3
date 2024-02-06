<?php

namespace App\Exports;

use App\Models\Siswa;
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
                "Kelas_tahun",
                "Tanggal_masuk",
                "Beasiswa",
                // data orang tua
                "Nama_ayah",
                "Nama_ibu",
                "Pendidikan_ayah",
                "Pendidikan_ibu",
                //pekerjaan
                "Pekerjaan_ayah",
                "Pekerjaan_ibu",
                //wali
                "Nama_wali",
                "Pekerjaan_wali",
                "Alamat_wali",
                //alamat
                "Rt",
                "Rw",
                "provinsi",
                "kabupaten",
                "kecamatan",
                "kelurahan",
                "nama_jalan",
                "Jenis Tinggal",
                "No HP",
        ];
    }
}
