<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExport implements FromView, WithHeadings
{
    public function view(): View
    {
        $siswas = Siswa::whereHas('kelas', function ($query) {
            $query->where('name', '!=', 'Lulus');
        })->get();

        // Ambil semua data lokasi di awal agar tidak looping query
        $provinsi = \DB::table('provinsi')->pluck('name', 'province_id');
        $kabupaten = \DB::table('kabupaten')->pluck('name', 'regency_id');
        $kecamatan = \DB::table('kecamatan')->pluck('name', 'district_id');
        $kelurahan = \DB::table('kelurahan')->pluck('name', 'village_id');

        // Transform data siswa
        $siswas->transform(function ($siswa) use ($provinsi, $kabupaten, $kecamatan, $kelurahan) {
            $siswa->provinsi = $provinsi[$siswa->provinsi_id] ?? '';
            $siswa->kabupaten = $kabupaten[$siswa->kabupaten_id] ?? '';
            $siswa->kecamatan = $kecamatan[$siswa->kecamatan_id] ?? '';
            $siswa->kelurahan = $kelurahan[$siswa->kelurahan_id] ?? '';
            return $siswa;
        });

        return view('dashboard.data.siswa.excel', compact('siswas'));
    }

    public function headings(): array
    {
        return [
            "No",
            "Nama",
            "Jenis Kelamin",
            "Tempat Lahir",
            "Tanggal Lahir",
            "Nisn",
            "Agama",
            "Kelas/tahun",
            "Tanggal Masuk",
            "Beasiswa",
            "Nama Ayah",
            "Nama Ibu",
            "Pendidikan Ayah",
            "Pendidikan Ibu",
            "Pekerjaan Ayah",
            "Pekerjaan Ibu",
            "Nama Wali",
            "Pekerjaan Wali",
            "Alamat Wali",
            "Rt",
            "Rw",
            "Provinsi",
            "Kabupaten",
            "Kecamatan",
            "Kelurahan",
            "Nama Jalan",
            "Jenis Tinggal",
            "No HP",
        ];
    }
}
