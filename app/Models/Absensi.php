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
        'keterangan',
        // ✅ TAMBAHAN UNTUK DEVICE TRACKING
        'ip_address',
        'user_agent',
        'device_id',
        'ip_address_pulang',
        'user_agent_pulang'
    ];


    /**
     * Relasi ke Karyawan
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    /**
     * Relasi ke Lokasi Absensi
     */
    public function lokasiAbsensi()
    {
        return $this->belongsTo(LokasiAbsensi::class);
    }

    /**
     * Relasi ke Jam Kerja
     */
    public function jamKerja()
    {
        return $this->belongsTo(JamKerja::class);
    }

    /**
     * Scope untuk filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter by bulan dan tahun
     */
    public function scopeByMonthYear($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)
                     ->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk absensi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status_masuk === 'terlambat') {
            return 'danger';
        } elseif ($this->status_pulang === 'pulang_cepat') {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Check if absensi is complete (masuk dan pulang)
     */
    public function isComplete()
    {
        return !is_null($this->jam_masuk) && !is_null($this->jam_pulang);
    }

    /**
     * Get durasi kerja in minutes
     */
    public function getDurasiKerjaMinutesAttribute()
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return 0;
        }

        $masuk = \Carbon\Carbon::parse($this->jam_masuk);
        $pulang = \Carbon\Carbon::parse($this->jam_pulang);

        return $masuk->diffInMinutes($pulang);
    }
}
