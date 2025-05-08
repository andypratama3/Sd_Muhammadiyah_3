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
        $path = database_path('seeders/data/siswa.csv');

        $csv = array_map(fn($line) => str_getcsv($line, ';'), file($path));
        $header = array_map('trim', $csv[0]);
        unset($csv[0]); // remove heade

        // Proses data siswa.csv
        foreach ($csv as $row) {
            $siswa_data = array_combine($header, $row);
            $nama = trim($siswa_data['nama']);

            $jenis_kelamin = $siswa_data['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
            $seragam = $jenis_kelamin === 'Laki-laki' ? 1450000 : 1650000;

            $nama = ucwords(strtolower($nama));

            $selected_data = $siswa_data['nama_ayah'] && $siswa_data['nama_ibu'] ? 'orang_tua' : 'wali';

            if ($siswa_data['provinsi']) {
                $provinsi = \DB::table('provinsi')->where('province_id', $siswa_data['provinsi'])->first();
                if ($provinsi) {
                    $kabupaten = \DB::table('kabupaten')->where('province_id', $provinsi->province_id)->where('regency_id', $siswa_data['kabupaten_kota'])->first();
                    if ($kabupaten) {
                        $kecamatan_value = str_replace('Kec.', '', $siswa_data['kecamatan']);
                        $kecamatan_value = trim($kecamatan_value);
                        $kecamatan = \DB::table('kecamatan')->where('regency_id', $kabupaten->regency_id)->where('name', 'like', '%' . $kecamatan_value . '%')->first();
                        if ($kecamatan) {
                            $kelurahan = \DB::table('kelurahan')->where('district_id', $kecamatan->district_id)->where('name', 'like', '%' . $siswa_data['kelurahan'] . '%')->first();
                            if ($kelurahan) {
                                $siswa_data['provinsi'] = $provinsi->province_id;
                                $siswa_data['kabupaten_kota'] = $kabupaten->regency_id;
                                $siswa_data['kecamatan'] = $kecamatan->district_id;
                                $siswa_data['kelurahan'] = $kelurahan->village_id;
                            }
                        }
                    }
                }
            } else {
                $siswa_data['provinsi'] = null;
                $siswa_data['kabupaten_kota'] = null;
                $siswa_data['kecamatan'] = null;
                $siswa_data['kelurahan'] = null;
            }


            $siswa = Siswa::create([
                'id' => Str::uuid(),
                'name' => $nama,
                'nisn' => $siswa_data['nisn'],
                'jk' => $jenis_kelamin,
                'tmpt_lahir' => $siswa_data['tempat_lahir'],
                'tgl_lahir' => $siswa_data['tanggal_lahir'],
                'agama' => $siswa_data['agama'],
                'spp' => $spp ?? null,
                'dpp' => $dpp ?? null,
                'seragam' => $seragam,
                'va_number' => null,
                'nama_pendidikan' => $siswa_data['nama_pendidikan'],
                'nama_jalan_pendidikan' => $siswa_data['alamat_pendidikan'],
                'kelas_tahun' => $siswa_data['kelas_tahun_ajaran'],
                'tanggal_masuk' => null,
                'beasiswa' => null,
                'foto' => 'asset_dashboard/img/default.jpg',
                'select_data' => $selected_data,
                'nama_ayah' => $siswa_data['nama_ayah'],
                'nama_ibu' => $siswa_data['nama_ibu'],
                'pendidikan_ayah' => $siswa_data['pendidikan_ayah'],
                'pendidikan_ibu' => $siswa_data['pendidikan_ibu'],
                'pekerjaan_ayah' => $siswa_data['pekerjaan_ayah'],
                'pekerjaan_ibu' => $siswa_data['pekerjaan_ibu'],
                'nama_wali' => $siswa_data['nama_wali'],
                'pekerjaan_wali' => $siswa_data['pekerjaan_wali'],
                'alamat_wali' => $siswa_data['alamat_wali'],
                'rt' => $siswa_data['rt'],
                'rw' => $siswa_data['rw'],
                'provinsi_id' => $siswa_data['provinsi'],
                'kabupaten_id' => $siswa_data['kabupaten_kota'],
                'kecamatan_id' => $siswa_data['kecamatan'],
                'kelurahan_id' => $siswa_data['kelurahan'],
                'jenis_tinggal' => $siswa_data['jenis_tinggal'],
                'nama_jalan' => $siswa_data['nama_jalan'],
                'no_hp' => $siswa_data['no_hp'],
                'slug' => Str::slug($nama . '-' . Str::random(5)),
            ]);

        }


    }

    private function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
