<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Siswa extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use SoftDeletes;

    protected $table = 'siswas';
    protected $fillable = [
        'name',
        'jk',
        'tmpt_lahir',
        'tgl_lahir',
        'nisn',
        'agama',
        // data sekolah
        'kelas_tahun',
        'tanggal_masuk',
        'beasiswa',
        'foto',
        // data orang tua
        'nama_ayah',
        'nama_ibu',
        'pendidikan_ayah',
        'pendidikan_ibu',
        //pekerjaan
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        //wali
        'nama_wali',
        'pekerjaan_wali',
        'alamat_wali',
        //alamat
        'rt',
        'rw',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'nama_jalan',
        'jenis_tinggal',
        'no_hp',
        'slug',
    ];
    protected $dates = ['deleted_at'];

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas')->withPivot('category_kelas');
    }
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id', 'id');
    }
}
