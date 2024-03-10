<?php
namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Jadwal;

class JudulPembayaranData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
    ){
        //
    }

    public static function formRequest(Jadwal $request)
    {
        return self::from([
            $request->getName(),
            $request->getSlug(),

        ]);
    }
    public static function messages()
    {
        return [
            'name.required' => 'Kolom Kategori Pembayaran tidak boleh kosong!',
        ];
    }
}
