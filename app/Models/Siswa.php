<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Siswa extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'siswas';
    protected $fillable = [
        'name',
        'nisn',
        'jk',
        'tmpt_lahir',
        'tgl_lahir',
        'nik',
        'agama',
        'rt',
        'rw',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'nama_jalan',
        'jenis_tinggal',
        'no_hp',
        'beasiswa',
        'foto',
        'slug',
    ];

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas')->withPivot('category_kelas');
    }
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id', 'id');
    }
}
