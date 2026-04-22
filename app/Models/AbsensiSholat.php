<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSholat extends Model
{
    use HasFactory;

    protected $table = 'absensi_sholat';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_sholat',
        'jenis_sholat',
        'latitude',
        'longitude',
        'area_name',
        'ip_address',
        'device_id',
        'user_agent',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function getNamaSholatAttribute(): string
    {
        return $this->jenis_sholat === 'duha' ? 'Sholat Duha' : 'Sholat Dzuhur';
    }
}
