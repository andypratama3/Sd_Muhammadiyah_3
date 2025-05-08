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
        $path_keuangan = database_path('seeders/data/data_keuangan.csv');

        $csv = array_map(fn($line) => str_getcsv($line, ';'), file($path));
        $header = array_map('trim', $csv[0]);
        unset($csv[0]); // remove header

        // Inisialisasi array keuangan
        $data_keuangan = [];
        $current_kelas = null;

        foreach (file($path_keuangan) as $line) {
            $line = trim($line);

            if (
                $line === '' ||
                str_starts_with($line, 'NO;') ||
                str_starts_with($line, ';;;') ||
                str_starts_with($line, ';')
            ) {
                continue;
            }

            // Deteksi baris penanda kelas
            if (preg_match('/^KELAS\s+\d+/i', $line)) {
                $current_kelas = preg_replace('/\s*;*$/', '', trim($line));
                $current_kelas = str_replace('KELAS', 'Kelas', $current_kelas);
                continue;
            }

            if (!$current_kelas) continue;

            $kelasModel = Kelas::where('name', $current_kelas)->first();
            if (!$kelasModel) continue;

            $category_kelas_array = json_decode($kelasModel->category_kelas, true) ?? [];
            $detected_category = null;

            foreach ($category_kelas_array as $category) {
                if (str_contains($line, $category)) {
                    $detected_category = $category;
                    break;
                }
            }

            $parts = array_map('trim', str_getcsv($line, ';')); // tambahkan trim di sini
            if (count($parts) >= 5) {
                $nama = $parts[1];
                $spp = (int) str_replace(['Rp', '.', ' '], '', $parts[2]);
                $dpp = (int) str_replace(['Rp', '.', ' '], '', $parts[3]);
                $keterangan = $parts[4];
                $normalized_nama = $this->normalizeName($nama);
                $data_keuangan[$kelasModel->name][$normalized_nama] = [
                    'original_name' => $nama,
                    'spp' => $spp,
                    'dpp' => $dpp,
                    'keterangan' => $keterangan,
                    'kelas' => $kelasModel->id,
                    'category_kelas' => $detected_category,
                ];
            }

        }

        // Proses data siswa.csv
        foreach ($csv as $row) {
            $siswa_data = array_combine($header, $row);
            $nama = trim($siswa_data['nama']);
            $normalized_nama = $this->normalizeName($nama);
            $keuangan = null;

            // Pencocokan langsung
            foreach ($data_keuangan as $kelas_data) {
                if (isset($kelas_data[$normalized_nama])) {
                    $keuangan = $kelas_data[$normalized_nama];
                    break;
                }
            }

            if (!$keuangan) {
                // Log siswa yang tidak ditemukan padanan keuangannya
                \Log::info("Data keuangan tidak ditemukan untuk siswa: {$nama}");
                continue; // Lewati siswa ini
            }


            // Fallback: pencocokan mirip jika tidak ditemukan
            if (!$keuangan) {
                foreach ($data_keuangan as $kelas_data) {
                    foreach ($kelas_data as $key_nama => $data) {
                        similar_text($normalized_nama, $key_nama, $percent);
                        if ($percent > 90) {
                            $keuangan = $data;
                            break 2;
                        }
                    }
                }
            }

            $spp = $keuangan['spp'];
            $dpp = $keuangan['dpp'];
            $kelas_id = $keuangan['kelas'];
            $category_kelas = $keuangan['category_kelas'];
            $jenis_kelamin = $siswa_data['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
            $seragam = $jenis_kelamin === 'Laki-laki' ? 1450000 : 1650000;

            $siswa = Siswa::create([
                'id' => Str::uuid(),
                'name' => $nama,
                'nisn' => $siswa_data['nisn'],
                'jk' => $jenis_kelamin,
                'tmpt_lahir' => $siswa_data['tempat_lahir'],
                'tgl_lahir' => $siswa_data['tanggal_lahir'],
                'agama' => $siswa_data['agama'],
                'spp' => $spp,
                'dpp' => $dpp,
                'seragam' => $seragam,
                'va_number' => null,
                'nama_pendidikan' => $siswa_data['nama_pendidikan'],
                'nama_jalan_pendidikan' => $siswa_data['alamat_pendidikan'],
                'kelas_tahun' => $siswa_data['kelas_tahun_ajaran'],
                'tanggal_masuk' => null,
                'beasiswa' => null,
                'foto' => null,
                'select_data' => 'orang_tua',
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

            if ($kelas_id) {
                $siswa->kelas()->attach($kelas_id, [
                    'category_kelas' => $category_kelas,
                ]);
            }
        }
    }

    private function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
// DELETE from siswa_kelas;
// DELETE from siswas;



// Awra Tsabita Humairah Jalil;0146087227;P;Bontang;2014-03-29;Islam;Islam Bunayya Samarinda;;;V;AL-QUDS;;;AL-QUDS;;;;Awaluddin Jalil;Taqdiraa;Diploma 3;Sarjana (S1);Jurnalis;Guru;;;;43;;;Loa Janan Ilir;Harapan Baru;;;Perumahan Tamansari Grand Samarinda, Cluster Kakaban H12/9;081134500616
// Rafa Nauval Gautama;0147303500;L;Samarinda;2014-04-14;Islam;Rasyiqah;;;4;2025-01-22;4 Bukhara;Bukhara;;;;Iqbal Gautama;Sita Munawarah;S1;D3;BUMN;Ibu Rumah tangga;;;;09;;;Samarinda Seberang;Mangkupalas;;;Jln Pattimura Blok V No 52 Samarinda Seberang;085250060439
// Alzea Adrena Saya;3168882847;P;Samarinda;2016-04-23;Islam;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

