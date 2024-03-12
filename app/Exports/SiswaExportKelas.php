<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;

class SiswaExportKelas implements FromView,WithHeadings
{
    protected $kelas_id, $category_kelas;

    public function __construct($kelas_id, $category_kelas)
    {
        $this->kelas_id = $kelas_id;
        $this->category_kelas = $category_kelas;
    }
    public function view(): View
    {
        if ($this->kelas_id && $this->category_kelas != null) {
            $siswas = Siswa::whereHas('kelas', function ($query) {
                $query->where('kelas.id', $this->kelas_id)
                      ->where('siswa_kelas.category_kelas', $this->category_kelas);
            })->with('kelas')->orderBy('name')->get();
        } else {
            $siswas = Siswa::whereHas('kelas', function ($query) {
                $query->where('kelas.id', $this->kelas_id);
            })->with('kelas')->orderBy('name')->get();
        }

    // Fetch province data
    $response_provinsi = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json");
    $provinsi = $response_provinsi->successful() ? collect($response_provinsi->json()) : [];

    // Transform student data
    $siswas->transform(function ($siswa) use ($provinsi) {
        $siswa->umur = now()->diffInYears($siswa->tgl_lahir);
        // Fetch regency (kabupaten) data
        $response_kabupaten = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/$siswa->provinsi_id.json");
        $kabupaten = $response_kabupaten->successful() ? collect($response_kabupaten->json()) : [];

        // Fetch district (kecamatan) data
        $response_kecamatan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/$siswa->kabupaten_id.json");
        $kecamatan = $response_kecamatan->successful() ? collect($response_kecamatan->json()) : [];

        // Fetch village (kelurahan) data
        $response_kelurahan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/$siswa->kecamatan_id.json");
        $kelurahan = $response_kelurahan->successful() ? collect($response_kelurahan->json()) : [];

        $provinsi_take = $provinsi->where('id', $siswa->provinsi_id)->first();
        $kabupaten_take = $kabupaten->where('id', $siswa->kabupaten_id)->first();
        $kecamatan_take = $kecamatan->where('id', $siswa->kecamatan_id)->first();
        $kelurahan_take = $kelurahan->where('id', $siswa->kelurahan_id)->first();

        $siswa->provinsi = $provinsi_take ? $provinsi_take['name'] : '';
        $siswa->kabupaten = $kabupaten_take ? $kabupaten_take['name'] : '';
        $siswa->kecamatan = $kecamatan_take ? $kecamatan_take['name'] : '';
        $siswa->kelurahan = $kelurahan_take ? $kelurahan_take['name'] : '';

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
