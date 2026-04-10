<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiAbsensi extends Model
{
    use HasFactory;
    protected $table = 'lokasi_absensi';

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius',
        'alamat',
        'status'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
