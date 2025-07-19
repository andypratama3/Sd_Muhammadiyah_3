<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class SiswaExportKelas implements FromView, WithHeadings
{
    protected $kelas_id, $category_kelas;

    public function __construct($kelas_id, $category_kelas)
    {
        $this->kelas_id = $kelas_id;
        $this->category_kelas = $category_kelas;
    }

    public function view(): View
    {
        // Ambil siswa sesuai filter
        $siswas = Siswa::whereHas('kelas', function ($query) {
            $query->where('kelas.id', $this->kelas_id);

            if ($this->category_kelas !== null) {
                $query->where('siswa_kelas.category_kelas', $this->category_kelas);
            }
        })
        ->with('kelas')
        ->orderBy('name')
        ->get();

        // Ambil semua wilayah dari database lokal (tidak pakai API)
        $provinsi = DB::table('provinsi')->pluck('name', 'province_id');
        $kabupaten = DB::table('kabupaten')->pluck('name', 'regency_id');
        $kecamatan = DB::table('kecamatan')->pluck('name', 'district_id');
        $kelurahan = DB::table('kelurahan')->pluck('name', 'village_id');

        // Transformasi data siswa
        $siswas->transform(function ($siswa) use ($provinsi, $kabupaten, $kecamatan, $kelurahan) {
            $siswa->umur = now()->diffInYears($siswa->tgl_lahir);

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
