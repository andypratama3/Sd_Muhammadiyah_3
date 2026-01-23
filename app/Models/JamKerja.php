<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    protected $table = 'jam_kerja';

    protected $fillable = [
        'nama_shift',
        'jenis_pegawai',
        'jam_masuk',
        'batas_masuk',
        'jam_pulang',
        'batas_pulang',
        'hari',
        'is_default'
    ];

    protected $casts = [
        'jam_masuk' => 'datetime:H:i',
        'batas_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'batas_pulang' => 'datetime:H:i',
        'is_default' => 'boolean',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
