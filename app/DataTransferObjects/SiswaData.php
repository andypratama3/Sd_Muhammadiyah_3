<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Siswa\SiswaRequest;


class SiswaData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $jk,
        public readonly string $tmpt_lahir,
        public readonly string $tgl_lahir,
        public readonly string $nisn,
        public readonly string $agama,
        public readonly ?string $nama_pendidikan,
        public readonly ?string $nama_jalan_pendidikan,
        public readonly string $kelas_tahun,
        public readonly string $tanggal_masuk,
        public readonly string $beasiswa,
        public readonly UploadedFile $foto,
        public readonly ?string $nama_ayah,
        public readonly ?string $nama_ibu,
        public readonly ?string $pendidikan_ayah,
        public readonly ?string $pendidikan_ibu,
        public readonly ?string $pekerjaan_ayah,
        public readonly ?string $pekerjaan_ibu,
        public readonly ?string $nama_wali,
        public readonly ?string $pekerjaan_wali,
        public readonly ?string $alamat_wali,
        public readonly string $rt,
        public readonly string $rw,
        public readonly string $provinsi_id,
        public readonly string $kabupaten_id,
        public readonly string $kecamatan_id,
        public readonly string $kelurahan_id,
        public readonly string $nama_jalan,
        public readonly string $jenis_tinggal,
        public readonly string $no_hp,
        public readonly ?string $slug,

        //relation parameter
        public readonly string $kelas,
        public readonly string $category_kelas,

    ) {
        //
    }

    public static function fromRequest(SiswaRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getJk(),
            $request->getTmptlahir(),
            $request->getTgllahir(),
            $request->getNisn(),
            $request->getAgama(),
            //pendidikan Sebelumnya
            $request->getNamaPendidikan(),
            $request->getJalanPendidikan(),
            $request->getKelasTahun(),
            $request->getTanggalMasuk(),
            $request->getBeasiswa(),
            $request->getFoto(),
            //request data orang tua
            $request->getNamaAyah(),
            $request->getNamaIbu(),
            $request->getPendidikanAyah(),
            $request->getPendidikanIbu(),
            $request->getPekerjaanAyah(),
            $request->getPekerjaanIbu(),
            $request->getNamaWali(),
            $request->getPekerjaanWali(),
            $request->getAlamatWali(),
            $request->getRt(),
            $request->getRw(),
            $request->getProvinsi(),
            $request->getKabupaten(),
            $request->getKecamatan(),
            $request->getKelurahan(),
            $request->getNamaJalan(),
            $request->getJenisTinggal(),
            $request->getNoHp(),
            $request->getSlug(),
            //relation data
            $request->getKelas(),
            $request->getCategoryKelas(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Artikel tidak boleh kosong!',
            'jk.required' => 'Kolom Jenis kelamin tidak boleh kosong!',
            'tmpt_lahir.required' => 'Kolom Tempat Lahir tidak boleh kosong!',
            'tgl_lahir.required' => 'Kolom tanggal Lahir tidak boleh kosong!',
            'nisn.required' => 'Kolom Nisn tidak boleh kosong!',
            'agama.required' => 'Kolom Agama tidak boleh kosong!',
            'kelas_tahun.required' => 'Kolom Kelas / Tahun tidak boleh kosong!',
            'tanggal_masu.required' => 'Kolom Tahun Masuk tidak boleh kosong!',
            'beasiswa.required' => 'Kolom Beasiswa tidak boleh kosong!',
            'foto.required' => 'Kolom File Foto tidak boleh kosong!',
            'foto.min: jpg,png' => 'Foto Minimal Jpg Atau Png',
            'nama_ayah.nullable' => 'Kolom Nama Ayah tidak boleh kosong!',
            'nama_ibu.nullable' => 'Kolom Nama Ibu tidak boleh kosong!',
            'pendidikan_ayah.nullable' => 'Kolom Pendidikan Ayah tidak boleh kosong!',
            'pendidikan_ibu.nullable' => 'Kolom Pendidikan Ibu tidak boleh kosong!',
            'pekerjaan_ayah.nullable' => 'Kolom Pekerjaan Ayah tidak boleh kosong!',
            'pekerjaan_ibu.nullable' => 'Kolom Pekerjaan Ibu tidak boleh kosong!',
            'nama_wali.nullable' => 'Kolom Nama Wali tidak boleh kosong!',
            'pekerjaan_wali.nullable' => 'Kolom Pekerjaan Wali tidak boleh kosong!',
            'alamat_wali.nullable' => 'Kolom Alamat Wali tidak boleh kosong!',
            'rt.required' => 'Kolom Rt tidak boleh kosong!',
            'rw.required' => 'Kolom Rw tidak boleh kosong!',
            'provinsi_id.required' => 'Kolom Provinsi tidak boleh kosong!',
            'kabupaten_id.required' => 'Kolom Kabupaten tidak boleh kosong!',
            'kecamatan_id.required' => 'Kolom Kecamatan tidak boleh kosong!',
            'kelurahan_id.required' => 'Kolom Kelurahan tidak boleh kosong!',
            'nama_jalan.required' => 'Kolom Nama Jalan tidak boleh kosong!',
            'jenis_tinggal.required' => 'Kolom Jenis Tinggal tidak boleh kosong!',
            'no_hp.required' => 'Kolom Hp tidak boleh kosong!',

            //relation message
            'kelas.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Category Kelas tidak boleh kosong!',
        ];
    }
}
