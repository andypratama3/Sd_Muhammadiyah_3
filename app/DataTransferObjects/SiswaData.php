<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Siswa\SiswaRequest;


class SiswaData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $nisn,
        public readonly string $jk,
        public readonly string $tmpt_lahir,
        public readonly string $tgl_lahir,
        public readonly string $nik,
        public readonly string $agama,
        public readonly string $rt,
        public readonly string $rw,
        public readonly string $provinsi_id,
        public readonly string $kabupaten_id,
        public readonly string $kecamatan_id,
        public readonly string $kelurahan_id,
        public readonly string $nama_jalan,
        public readonly string $jenis_tinggal,
        public readonly string $no_hp,
        public readonly string $beasiswa,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(SiswaRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getNisn(),
            $request->getJk(),
            $request->getTmptlahir(),
            $request->getTgllahir(),
            $request->getNik(),
            $request->getAgama(),
            $request->getRt(),
            $request->getRw(),
            $request->getProvinsi(),
            $request->getKabupaten(),
            $request->getKecamatan(),
            $request->getKelurahan(),
            $request->getNamaJalan(),
            $request->getJenisTinggal(),
            $request->getNoHp(),
            $request->getBeasiswa(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Artikel tidak boleh kosong!',
            'nisn.required' => 'Kolom Nisn tidak boleh kosong!',
            'jk.required' => 'Kolom Jenis kelamin tidak boleh kosong!',
            'tmpt_lahir.required' => 'Kolom Tempat Lahir tidak boleh kosong!',
            'tgl_lahir.required' => 'Kolom tanggal Lahir tidak boleh kosong!',
            'nik.required' => 'Kolom Nik tidak boleh kosong!',
            'agama.required' => 'Kolom Agama tidak boleh kosong!',
            'rt.required' => 'Kolom Rt tidak boleh kosong!',
            'rw.required' => 'Kolom Rw tidak boleh kosong!',
            'provinsi_id.required' => 'Kolom Provinsi tidak boleh kosong!',
            'kabupaten_id.required' => 'Kolom Kabupaten tidak boleh kosong!',
            'kecamatan_id.required' => 'Kolom Kecamatan tidak boleh kosong!',
            'kelurahan_id.required' => 'Kolom Kelurahan tidak boleh kosong!',
            'nama_jalan.required' => 'Kolom Nama Jalan tidak boleh kosong!',
            'jenis_tinggal.required' => 'Kolom Jenis Tinggal tidak boleh kosong!',
            'no_hp.required' => 'Kolom Hp tidak boleh kosong!',
            'beasiswa.required' => 'Kolom Beasiswa tidak boleh kosong!',
            'foto.required' => 'Kolom File Foto tidak boleh kosong!',
        ];
    }
}
