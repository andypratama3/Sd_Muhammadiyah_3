<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeviceAbsensi extends Model
{
    use HasFactory;

    protected $table = 'device_absensi';

    protected $fillable = [
        'karyawan_id',
        'device_fingerprint',
        'device_name',
        'device_id',
        'ip_address',
        'user_agent',
        'is_active',
        'last_used_at',
        'registered_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'registered_at' => 'datetime'
    ];

    /**
     * Relasi ke Karyawan
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Scope untuk device aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk device yang terakhir digunakan dalam X hari
     */
    public function scopeRecentlyUsed($query, $days = 30)
    {
        return $query->where('last_used_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Check apakah device sudah lama tidak digunakan
     */
    public function isStale($days = 90)
    {
        if (!$this->last_used_at) {
            return true;
        }

        return $this->last_used_at->lt(Carbon::now()->subDays($days));
    }

    /**
     * Get formatted last used time
     */
    public function getLastUsedFormattedAttribute()
    {
        if (!$this->last_used_at) {
            return 'Belum pernah digunakan';
        }

        return $this->last_used_at->diffForHumans();
    }

    /**
     * Get formatted registered time
     */
    public function getRegisteredFormattedAttribute()
    {
        if (!$this->registered_at) {
            return '-';
        }

        return $this->registered_at->format('d M Y H:i');
    }
}
