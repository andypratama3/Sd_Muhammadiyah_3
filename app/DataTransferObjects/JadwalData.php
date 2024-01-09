<?php
namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Jadwal;

class JadwalData extends Data
{
    public function __construct(
        public readonly string $tahun_ajaran,
        public readonly UploadedFile $jadwal,
        public readonly string $kelas,
        public readonly string $category_kelas,
        public readonly ?string $slug,
    ){
        //
    }

    public static function formRequest(Jadwal $request)
    {
        return self::from([
            $request->getTahun_Ajaran(),
            $request->getJadwal(),
            $request->getKelas(),
            $request->getCategoryKelas(),
            $request->getSlug(),

        ]);
    }
    public static function messages()
    {
        return [
            'tahun_ajaran.required' => 'Kolom Tahun Ajaran tidak boleh kosong!',
            'jadwal.required' => 'Kolom Jadwal tidak boleh kosong!',
            'Kelas.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas tidak boleh kosong!',
        ];
    }
}
