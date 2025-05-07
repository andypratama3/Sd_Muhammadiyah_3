<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // $path = database_path('seeders/data/siswa.csv');

        // $csv = array_map(fn($line) => str_getcsv($line, ';'), file($path));
        // $header = array_map('trim', $csv[0]);
        // unset($csv[0]); // remove heade

        // // Proses data siswa.csv
        // foreach ($csv as $row) {
        //     $siswa_data = array_combine($header, $row);
        //     $nama = trim($siswa_data['nama']);

        //     $jenis_kelamin = $siswa_data['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
        //     $seragam = $jenis_kelamin === 'Laki-laki' ? 1450000 : 1650000;

        //     $nama = ucwords(strtolower($nama));

        //     $selected_data = $siswa_data['nama_ayah'] && $siswa_data['nama_ibu'] ? 'orang_tua' : 'wali';

        //     if ($siswa_data['provinsi']) {
        //         $provinsi = \DB::table('provinsi')->where('province_id', $siswa_data['provinsi'])->first();
        //         if ($provinsi) {
        //             $kabupaten = \DB::table('kabupaten')->where('province_id', $provinsi->province_id)->where('regency_id', $siswa_data['kabupaten_kota'])->first();
        //             if ($kabupaten) {
        //                 $kecamatan_value = str_replace('Kec.', '', $siswa_data['kecamatan']);
        //                 $kecamatan_value = trim($kecamatan_value);
        //                 $kecamatan = \DB::table('kecamatan')->where('regency_id', $kabupaten->regency_id)->where('name', 'like', '%' . $kecamatan_value . '%')->first();
        //                 if ($kecamatan) {
        //                     $kelurahan = \DB::table('kelurahan')->where('district_id', $kecamatan->district_id)->where('name', 'like', '%' . $siswa_data['kelurahan'] . '%')->first();
        //                     if ($kelurahan) {
        //                         $siswa_data['provinsi'] = $provinsi->province_id;
        //                         $siswa_data['kabupaten_kota'] = $kabupaten->regency_id;
        //                         $siswa_data['kecamatan'] = $kecamatan->district_id;
        //                         $siswa_data['kelurahan'] = $kelurahan->village_id;
        //                     }
        //                 }
        //             }
        //         }
        //     } else {
        //         $siswa_data['provinsi'] = null;
        //         $siswa_data['kabupaten_kota'] = null;
        //         $siswa_data['kecamatan'] = null;
        //         $siswa_data['kelurahan'] = null;
        //     }


        //     $siswa = Siswa::create([
        //         'id' => Str::uuid(),
        //         'name' => $nama,
        //         'nisn' => $siswa_data['nisn'],
        //         'jk' => $jenis_kelamin,
        //         'tmpt_lahir' => $siswa_data['tempat_lahir'],
        //         'tgl_lahir' => $siswa_data['tanggal_lahir'],
        //         'agama' => $siswa_data['agama'],
        //         'spp' => $spp ?? null,
        //         'dpp' => $dpp ?? null,
        //         'seragam' => $seragam,
        //         'va_number' => null,
        //         'nama_pendidikan' => $siswa_data['nama_pendidikan'],
        //         'nama_jalan_pendidikan' => $siswa_data['alamat_pendidikan'],
        //         'kelas_tahun' => $siswa_data['kelas_tahun_ajaran'],
        //         'tanggal_masuk' => null,
        //         'beasiswa' => null,
        //         'foto' => 'asset_dashboard/img/default.jpg',
        //         'select_data' => $selected_data,
        //         'nama_ayah' => $siswa_data['nama_ayah'],
        //         'nama_ibu' => $siswa_data['nama_ibu'],
        //         'pendidikan_ayah' => $siswa_data['pendidikan_ayah'],
        //         'pendidikan_ibu' => $siswa_data['pendidikan_ibu'],
        //         'pekerjaan_ayah' => $siswa_data['pekerjaan_ayah'],
        //         'pekerjaan_ibu' => $siswa_data['pekerjaan_ibu'],
        //         'nama_wali' => $siswa_data['nama_wali'],
        //         'pekerjaan_wali' => $siswa_data['pekerjaan_wali'],
        //         'alamat_wali' => $siswa_data['alamat_wali'],
        //         'rt' => $siswa_data['rt'],
        //         'rw' => $siswa_data['rw'],
        //         'provinsi_id' => $siswa_data['provinsi'],
        //         'kabupaten_id' => $siswa_data['kabupaten_kota'],
        //         'kecamatan_id' => $siswa_data['kecamatan'],
        //         'kelurahan_id' => $siswa_data['kelurahan'],
        //         'jenis_tinggal' => $siswa_data['jenis_tinggal'],
        //         'nama_jalan' => $siswa_data['nama_jalan'],
        //         'no_hp' => $siswa_data['no_hp'],
        //         'slug' => Str::slug($nama . '-' . Str::random(5)),
        //     ]);

        // }

    // Insert Awra Tsabita Humairah Jalil
    // Siswa::create([
    //     'id' => Str::uuid(),
    //     'name' => 'Awra Tsabita Humairah Jalil',
    //     'nisn' => '0146087227',
    //     'jk' => 'Perempuan', // Female
    //     'tmpt_lahir' => 'Bontang',
    //     'tgl_lahir' => '2014-03-29',
    //     'agama' => 'Islam',
    //     'spp' => null,
    //     'dpp' => null,
    //     'seragam' => 1650000,
    //     'va_number' => null,
    //     'nama_pendidikan' => 'AL-QUDS',
    //     'nama_jalan_pendidikan' => 'AL-QUDS',
    //     'kelas_tahun' => '',
    //     'tanggal_masuk' => null,
    //     'beasiswa' => null,
    //     'foto' => 'asset_dashboard/img/default.jpg',
    //     'select_data' => 'orang_tua', // You can set a default value or leave as null
    //     'nama_ayah' => 'Awaluddin Jalil',
    //     'nama_ibu' => 'Taqdiraa',
    //     'pendidikan_ayah' => 'Diploma 3',
    //     'pendidikan_ibu' => 'Sarjana (S1)',
    //     'pekerjaan_ayah' => 'Jurnalis',
    //     'pekerjaan_ibu' => 'Guru',
    //     'nama_wali' => null, // No wali data
    //     'pekerjaan_wali' => null, // No wali data
    //     'alamat_wali' => 'Loa Janan Ilir, Harapan Baru, Perumahan Tamansari Grand Samarinda, Cluster Kakaban H12/9',
    //     'rt' => null,
    //     'rw' => null,
    //     'provinsi_id' => 64,
    //     'kabupaten_id' => 'Kota Samarinda',
    //     'kecamatan_id' => 'Kec. Loa Janan Ilir',
    //     'kelurahan_id' => 'Desa Harapan Baru',
    //     'jenis_tinggal' => null,
    //     'nama_jalan' => null,
    //     'no_hp' => '081134500616',
    //     'slug' => Str::slug('Awra Tsabita Humairah Jalil' . '-' . Str::random(5)),
    // ]);

    // // Insert Rafa Nauval Gautama
    // Siswa::create([
    //     'id' => Str::uuid(),
    //     'name' => 'Rafa Nauval Gautama',
    //     'nisn' => '0147303500',
    //     'jk' => 'Laki-laki', // Male
    //     'tmpt_lahir' => 'Samarinda',
    //     'tgl_lahir' => '2014-04-14',
    //     'agama' => 'Islam',
    //     'spp' => null,
    //     'dpp' => null,
    //     'seragam' => 1450000,
    //     'va_number' => null,
    //     'nama_pendidikan' => '',
    //     'nama_jalan_pendidikan' => '',
    //     'kelas_tahun' => null,
    //     'tanggal_masuk' => null,
    //     'beasiswa' => null,
    //     'foto' => 'asset_dashboard/img/default.jpg',
    //     'select_data' => 'orang_tua', // You can set a default value or leave as null
    //     'nama_ayah' => 'Iqbal Gautama',
    //     'nama_ibu' => 'Sita Munawarah',
    //     'pendidikan_ayah' => 'S1',
    //     'pendidikan_ibu' => 'D3',
    //     'pekerjaan_ayah' => 'BUMN',
    //     'pekerjaan_ibu' => 'Ibu Rumah tangga',
    //     'nama_wali' => null, // No wali data
    //     'pekerjaan_wali' => null, // No wali data
    //     'alamat_wali' => 'Jln Pattimura Blok V No 52 Samarinda Seberang',
    //     'rt' => null,
    //     'rw' => null,
    //     'provinsi_id' => 64,
    //     'kabupaten_id' => 'Samarinda',
    //     'kecamatan_id' => 'Loa Janan Ilir',
    //     'kelurahan_id' => 'Rapak Dalam',
    //     'jenis_tinggal' => 'Bersama Orang Tua',
    //     'nama_jalan' => 'Jln Pattimura Blok V No 52 Samarinda Seberang',
    //     'no_hp' => '085250060439',
    //     'slug' => Str::slug('Rafa Nauval Gautama' . '-' . Str::random(5)),
    // ]);

    // Insert Alzea Adrena Saya
    // Siswa::create([
    //     'id' => Str::uuid(),
    //     'name' => 'Alzea Adrena Saya',
    //     'nisn' => '3168882847',
    //     'jk' => 'Perempuan', // Female
    //     'tmpt_lahir' => 'Samarinda',
    //     'tgl_lahir' => '2016-04-23',
    //     'agama' => 'Islam',
    //     'spp' => null,
    //     'dpp' => null,
    //     'seragam' => 1650000, // No seragam data
    //     'va_number' => '1234567890',
    //     'nama_pendidikan' => 'Dummy Sekolah',
    //     'nama_jalan_pendidikan' => 'Jalan Pendidikan No 1',
    //     'kelas_tahun' => '2025-2026',
    //     'tanggal_masuk' => '2025-07-01',
    //     'beasiswa' => 'Dummy Beasiswa',
    //     'foto' => 'asset_dashboard/img/dummy.jpg',
    //     'select_data' => 'orang_tua',
    //     'nama_ayah' => 'Dummy Ayah',
    //     'nama_ibu' => 'Dummy Ibu',
    //     'pendidikan_ayah' => 'Dummy Pendidikan Ayah',
    //     'pendidikan_ibu' => 'Dummy Pendidikan Ibu',
    //     'pekerjaan_ayah' => 'Dummy Pekerjaan Ayah',
    //     'pekerjaan_ibu' => 'Dummy Pekerjaan Ibu',
    //     'nama_wali' => 'Dummy Wali',
    //     'pekerjaan_wali' => 'Dummy Pekerjaan Wali',
    //     'alamat_wali' => 'Jalan Wali No 1',
    //     'rt' => '1',
    //     'rw' => '2',
    //     'provinsi_id' => 64,
    //     'kabupaten_id' => 'Kota Samarinda',
    //     'kecamatan_id' => 'Samarinda Seberang',
    //     'kelurahan_id' => 'Sungai Keledang',
    //     'jenis_tinggal' => 'Dummy Jenis Tinggal',
    //     'nama_jalan' => 'Jalan Dummy No 1',
    //     'no_hp' => '081234567890',
    //     'slug' => Str::slug('Alzea Adrena Saya' . '-' . Str::random(5)),
    //     ]);

    }

    private function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
