<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    use HasFactory;
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


    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
