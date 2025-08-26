<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // check siswa
        $siswa = Siswa::where('nisn', $row['nisn'])->first();

        if (!$siswa) {
            // Konversi romawi ke angka
            $kelasMap = [
                'I' => 1,
                'II' => 2,
                'III' => 3,
                'IV' => 4,
                'V' => 5,
                'VI' => 6,
            ];

            $tanggal_lahir = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_lahir']));
            $tanggal_lahir = $tanggal_lahir->format('Y-m-d');
            $jenis_kelamin = collect();

            $seragam = $row['seragam'] ?? 0;

            // condition jenis kelamin
            if($row['jk'] == 'L') {
                $jenis_kelamin = 'Laki-laki';
            } else if($row['jk'] == 'P') {
                $jenis_kelamin = 'Perempuan';
            }

            //( make when seragam is Laki-laki
            if($row['seragam'] == null) {
                if($jenis_kelamin == 'Laki-laki') {
                    $seragam = 1450000;
                } else {
                    $seragam = 1650000;
                }
            }

            $kelasInput = strtoupper(trim($row['kelas']));
            $kelasAngka = $kelasMap[$kelasInput] ?? null;

            // Jika tidak ditemukan di map, coba ambil angka dari string (misal "Kelas 3")
            if (!$kelasAngka && preg_match('/\d+/', $kelasInput, $match)) {
                $kelasAngka = (int)$match[0];
            }

            if (!$kelasAngka) {
                return null;
            }


            $kelasName = 'Kelas ' . $kelasAngka;
            $kelas = Kelas::where('name', $kelasName)->first();

            if (!$kelas) {
                return null; // skip jika kelas tidak ditemukan
            }

            $kelas_id = $kelas->id;

            $tahunSekarang = date('Y');
            $tahunMasuk = $tahunSekarang - ($kelasAngka - 1);
            $tanggal_masuk = Carbon::create($tahunMasuk, 7, 1, 0, 0, 0)->format('Y-m-d');

            // Cek lokasi jika tersedia
            $provinsi_id = 64;
            $kabupaten_id = collect();
            $kecamatan_id = collect();
            $kelurahan_id = collect();

            if (!empty($provinsi_id)) {
                $provinsi = DB::table('provinsi')->where('province_id', $provinsi_id)->first();

                if ($provinsi) {
                    $kabupaten = DB::table('kabupaten')
                        ->where('province_id', $provinsi->province_id)
                        ->where('name', 'like', '%' . 'Samarinda' . '%')->first();

                    if ($kabupaten) {
                        $kecamatan_value = trim(str_replace('Kec.', '', $row['kecamatan']));
                        $kecamatan = DB::table('kecamatan')
                            ->where('regency_id', $kabupaten->regency_id)
                            ->where('name', 'like', '%' . $kecamatan_value . '%')->first();


                        if ($kecamatan) {
                            $kelurahan = DB::table('kelurahan')
                                ->where('district_id', $kecamatan->district_id)
                                ->where('name', 'like', '%' . $row['kelurahan'] . '%')->first();


                            $provinsi_id = $provinsi->province_id;
                            $kabupaten_id = $kabupaten->regency_id;
                            $kecamatan_id = $kecamatan->district_id;
                            $kelurahan_id = $kelurahan->village_id ?? null;
                        }
                    }
                }
            }

            $select_data = (!empty($row['nama_ayah']) && !empty($row['nama_ibu'])) ? 'orang_tua' : 'wali';


            // nisn replace dot
            $nisn = $row['nisn'];
            $nisn = str_replace('.', '', $nisn);




            // Simpan siswa
            $siswa = Siswa::create([
                'name'                  => $row['name'],
                'jk'                    => $jenis_kelamin,
                'tmpt_lahir'            => $row['tmpt_lahir'],
                'tgl_lahir'             => $tanggal_lahir,
                'nisn'                  => $nisn,
                'va_number'             => $row['nisn'],
                'agama'                 => $row['agama'],
                'spp'                   => (int) str_replace('.', '', $row['spp']),
                'dpp'                   => (int) str_replace('.', '', $row['dpp']),
                'seragam'               => (int) str_replace('.', '', $seragam),
                'nama_pendidikan'       => $row['nama_pendidikan'],
                'nama_jalan_pendidikan' => $row['nama_jalan_pendidikan'],
                'kelas'                 => $kelasName,
                'kelas_tahun_ajaran'    => $tahunMasuk,
                'tanggal_masuk'         => $tanggal_masuk,
                'beasiswa'              => $row['beasiswa'] ?? null,
                'select_data'           => $select_data,
                'nama_ayah'             => $row['nama_ayah'],
                'nama_ibu'              => $row['nama_ibu'],
                'pendidikan_ayah'       => $row['pendidikan_ayah'],
                'pendidikan_ibu'        => $row['pendidikan_ibu'],
                'pekerjaan_ayah'        => $row['pekerjaan_ayah'],
                'pekerjaan_ibu'         => $row['pekerjaan_ibu'],
                'nama_wali'             => $row['nama_wali'],
                'pekerjaan_wali'        => $row['pekerjaan_wali'],
                'alamat_wali'           => $row['alamat_wali'] ?? $row['nama_jalan'],
                'rt'                    => $row['rt'] ?? 0,
                'rw'                    => $row['rw'] ?? 0,
                'provinsi_id'           => $provinsi_id,
                'kabupaten_id'          => $kabupaten_id,
                'kecamatan_id'          => $kecamatan_id,
                'kelurahan_id'          => $kelurahan_id,
                'nama_jalan'            => $row['nama_jalan'],
                'jenis_tinggal'         => $row['jenis_tinggal'],
                'no_hp'                 => $row['no_hp'] ?? null,
                'foto'                  => null,
            ]);

            // Relasi ke kelas
            $category_kelas = $row['category_kelas'] ?? null;
            $siswa->kelas()->sync([
                $kelas_id => ['category_kelas' => $category_kelas],
            ]);

            return $siswa;

        } else {
            $siswa = Siswa::where('nisn', $row['nisn'])->first();
            if (!$siswa) return null;

            $kelasInput = strtoupper(trim($row['kelas']));
            $kelasMap = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6];
            $kelasAngka = $kelasMap[$kelasInput] ?? (preg_match('/\d+/', $kelasInput, $m) ? (int) $m[0] : null);
            if (!$kelasAngka) return null;

            $kelas = Kelas::where('name', 'Kelas ' . $kelasAngka)->first();
            if (!$kelas) return null;

            // Ambil data category_kelas
            $categoryRaw = $kelas->category_kelas;

            // Coba decode json
            $categoryExplode = json_decode($categoryRaw, true);

            // Jika gagal decode, lakukan explode manual
            if (!is_array($categoryExplode)) {
                $categoryExplode = array_map(function ($val) {
                    return trim($val, "\"[] ");
                }, explode(',', $categoryRaw));
            }

            // Cari kecocokan tanpa membuat kategori baru
            $category_kelas = null;
            foreach ($categoryExplode as $existingCategory) {
                if (strcasecmp(trim($existingCategory), trim($row['category_kelas'])) === 0) {
                    $category_kelas = $existingCategory;
                    break;
                }
            }

            // Update nilai spp, dpp, seragam
            $siswa->update([
                'spp' => (int) str_replace('.', '', $row['spp']),
                'dpp' => (int) str_replace('.', '', $row['dpp']),
                'seragam' => (int) str_replace('.', '', $row['seragam']),
            ]);

            // Sinkronisasi relasi kelas
            $siswa->kelas()->sync([
                $kelas->id => [
                    'category_kelas' => $category_kelas,
                ],
            ]);

            return null;


        }

    }
}
