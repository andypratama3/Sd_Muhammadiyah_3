<?php
namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Jadwal\JadwalRequest;

class JadwalData extends Data
{
    public function __construct(
        public readonly string $tahun_ajaran,
        public readonly ?UploadedFile $jadwal_file,
        public readonly string $kelas,
        public readonly string $category_kelas,
        public readonly ?string $slug,
        public readonly ?array $jadwal,
    ){
        //
    }

    public static function formRequest(JadwalRequest $request)
    {
        return self::from([
            $request->getTahun_Ajaran(),
            $request->getJadwal(),
            $request->getKelas(),
            $request->getCategoryKelas(),
            $request->getSlug(),
            $request->getJadwalDetail(),

        ]);
    }
    public static function messages()
    {
        return [
            'tahun_ajaran.required' => 'Kolom Tahun Ajaran tidak boleh kosong!',
            'Kelas.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas tidak boleh kosong!',
        ];
    }
}
