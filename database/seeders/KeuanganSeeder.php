<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class KeuanganSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/keuangan_baru.csv');
        $csv = array_map(fn($line) => str_getcsv($line, ';'), file($path));
        $header = array_map('trim', $csv[0]);
        unset($csv[0]);

        // Reset SPP and DPP for all students
        Siswa::query()->update(['spp' => null, 'dpp' => null]);

        // Load all students for fallback matching
        $allSiswa = Siswa::all();

        foreach ($csv as $row) {
            $data = array_combine($header, $row);





            $kelas = Kelas::where('name', trim($data['kelas']))->first();
            if (!$kelas) {
                echo "❌ Kelas tidak ditemukan: {$data['kelas']}\n";
                continue;
            }

            $searchNama = Str::lower(trim($data['nama']));

            // Try SQL-style LIKE matching
            $siswa = Siswa::where('name', trim($data['nama']))->first();


            // Fallback to similar_text if LIKE fails
            if (!$siswa) {
                $normalizedNama = $this->normalizeName($data['nama']);
                $siswa = $allSiswa->first(function ($item) use ($normalizedNama) {
                    $dbName = $this->normalizeName($item->name);
                    similar_text($dbName, $normalizedNama, $percent);
                    return $percent >= 90;
                });
            }


            if (!$siswa) {
                echo "❌ Siswa tidak ditemukan: {$data['nama']}\n";
                // // create new siswa
                // $siswa = Siswa::create([
                //     'id' => Str::uuid(),
                //     'name' => $nama,
                //     'nisn' => $siswa_data['nisn'],
                //     'jk' => $jenis_kelamin,
                //     'tmpt_lahir' => $siswa_data['tempat_lahir'],
                //     'tgl_lahir' => $siswa_data['tanggal_lahir'],
                //     'agama' => $siswa_data['agama'],
                //     'spp' => $spp ?? null,
                //     'dpp' => $dpp ?? null,
                //     'seragam' => $seragam,
                //     'va_number' => null,
                //     'nama_pendidikan' => $siswa_data['nama_pendidikan'],
                //     'nama_jalan_pendidikan' => $siswa_data['alamat_pendidikan'],
                //     'kelas_tahun' => $siswa_data['kelas_tahun_ajaran'],
                //     'tanggal_masuk' => null,
                //     'beasiswa' => null,
                //     'foto' => asset('asset_dashboard/img/default.jpg'),
                //     'select_data' => $selected_data,
                //     'nama_ayah' => $siswa_data['nama_ayah'],
                //     'nama_ibu' => $siswa_data['nama_ibu'],
                //     'pendidikan_ayah' => $siswa_data['pendidikan_ayah'],
                //     'pendidikan_ibu' => $siswa_data['pendidikan_ibu'],
                //     'pekerjaan_ayah' => $siswa_data['pekerjaan_ayah'],
                //     'pekerjaan_ibu' => $siswa_data['pekerjaan_ibu'],
                //     'nama_wali' => $siswa_data['nama_wali'],
                //     'pekerjaan_wali' => $siswa_data['pekerjaan_wali'],
                //     'alamat_wali' => $siswa_data['alamat_wali'],
                //     'rt' => $siswa_data['rt'],
                //     'rw' => $siswa_data['rw'],
                //     'provinsi_id' => $siswa_data['provinsi'],
                //     'kabupaten_id' => $siswa_data['kabupaten_kota'],
                //     'kecamatan_id' => $siswa_data['kecamatan'],
                //     'kelurahan_id' => $siswa_data['kelurahan'],
                //     'jenis_tinggal' => $siswa_data['jenis_tinggal'],
                //     'nama_jalan' => $siswa_data['nama_jalan'],
                //     'no_hp' => $siswa_data['no_hp'],
                //     'slug' => Str::slug($nama . '-' . Str::random(5)),
                // ]);

                continue;
            }



            $category_kelas = trim($data['category_kelas']) ?? null;
            $spp = (int) str_replace(['Rp', '.', ' '], '', $data['spp']) ?: 0;
            $dpp = (int) str_replace(['Rp', '.', ' '], '', $data['dpp']) ?: 0;

            $siswa->update([
                'spp' => $spp,
                'dpp' => $dpp,
            ]);

            $siswa->kelas()->sync([
                $kelas->id => ['category_kelas' => $category_kelas],
            ]);

            echo "✅ Berhasil update: {$siswa->name}, SPP: {$spp}, DPP: {$dpp}, Kelas ID: {$kelas->id}, Kategori: {$category_kelas}\n";
        }
    }

    // Normalize name to lowercase and remove non-alphanumeric characters
    private function normalizeName(string $name): string
    {
        return Str::lower(preg_replace('/[^a-z0-9]/i', '', $name));
    }
}
