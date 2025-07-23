<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaKelasImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        // checked from nisn
        $siswa = Siswa::where('nisn', $row['nisn'])->first();

        if (!$siswa) {
            return null;
        }
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


        // Sinkronisasi relasi kelas
        $siswa->kelas()->sync([
            $kelas->id => [
                'category_kelas' => $category_kelas,
            ],
        ]);

        return null;
    }
}
