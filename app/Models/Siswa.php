<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;
    use UsesUuid;
    protected $tables = 'siswas';
    protected $fillable = [
        'name',
        'jk',
        'tmpt_lahir',
        'tgl_lahir',
        'nik',
        'agama',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kodepos',
        'jenis_tinggal',
        'no_hp',
        'beasiswa',
        'foto',
        'slug',
    ];

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'siswas_kelas');
    }
}
