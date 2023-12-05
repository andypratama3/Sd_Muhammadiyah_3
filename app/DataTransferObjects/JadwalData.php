<?php
namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Jadwal;

class JadwalData extends Data
{
    public function __construct(
        public readonly string $smester,
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
            $request->getSmester(),
            $request->getJadwal(),
            $request->getKelas(),
            $request->getCategoryKelas(),
            $request->getSlug(),

        ]);
    }
    public static function messages()
    {
        return [
            'smester.required' => 'Kolom Smester tidak boleh kosong!',
            'jadwal.required' => 'Kolom Jadwal Kelamin tidak boleh kosong!',
            'Kelas.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas tidak boleh kosong!',
        ];
    }
}
