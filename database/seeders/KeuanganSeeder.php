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
