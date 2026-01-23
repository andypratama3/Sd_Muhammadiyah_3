<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'lokasi_absensi_id',
        'jam_kerja_id',
        'tanggal',
        'status_kehadiran',
        'jam_masuk',
        'latitude_masuk',
        'longitude_masuk',
        'jarak_masuk',
        'status_masuk',
        'jam_pulang',
        'latitude_pulang',
        'longitude_pulang',
        'jarak_pulang',
        'status_pulang',
        'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_pulang' => 'decimal:8',
        'longitude_pulang' => 'decimal:8',
        'jarak_masuk' => 'decimal:2',
        'jarak_pulang' => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function lokasiAbsensi()
    {
        return $this->belongsTo(LokasiAbsensi::class);
    }

    public function jamKerja()
    {
        return $this->belongsTo(JamKerja::class);
    }
}
