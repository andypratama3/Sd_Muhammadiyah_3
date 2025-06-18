<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;

class SiswaExport implements FromView,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function view(): View
    {
        $siswas = Siswa::whereHas('kelas', function ($query) {
            $query->where('name', '!=', 'Lulus');
        });

        // Fetch province data
        $response_provinsi = \DB::table('provinsi')->orderBy('name')->get();
        $provinsi = $response_provinsi ? collect($response_provinsi->toArray()) : [];

        // Transform student data
        $siswas = $siswas->get()->map(function ($siswa) use ($provinsi) {
            $siswa->umur = now()->diffInYears($siswa->tgl_lahir);
            // Fetch regency (kabupaten) data

            $response_kabupaten = \DB::table('kabupaten')->where('province_id', $siswa->province_id)->get();
            $kabupaten = $response_kabupaten ? collect($response_kabupaten->toArray()) : [];

            // Fetch district (kecamatan) data
            $response_kecamatan = \DB::table('kecamatan')->where('regency_id', $siswa->kabupaten_id)->get();
            $kecamatan = $response_kecamatan ? collect($response_kecamatan->toArray()) : [];

            // Fetch village (kelurahan) data
            $response_kelurahan = \DB::table('kelurahan')->where('district_id', $siswa->kecamatan_id)->get();
            $kelurahan = $response_kelurahan ? collect($response_kelurahan->toArray()) : [];

            $provinsi_take = $provinsi->where('province_id', $siswa->provinsi_id)->first();
            $kabupaten_take = $kabupaten->where('regency_id', $siswa->kabupaten_id)->first();
            $kecamatan_take = $kecamatan->where('district_id', $siswa->kecamatan_id)->first();
            $kelurahan_take = $kelurahan->where('village_id', $siswa->kelurahan_id)->first();

            $siswa->provinsi = $provinsi_take ? $provinsi_take->name : '';
            $siswa->kabupaten = $kabupaten_take ? $kabupaten_take->name : '';
            $siswa->kecamatan = $kecamatan_take ? $kecamatan_take->name : '';
            $siswa->kelurahan = $kelurahan_take ? $kelurahan_take->name : '';

            return $siswa;
        });
        // Pass data to the view
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
