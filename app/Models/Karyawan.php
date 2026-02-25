<?php

namespace App\Models;

use Str;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use UsesUuid;
    use SoftDeletes;

    protected $table = 'karyawans';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'sex',
        'phone',
        'slug',
        'nip',
        'user_id',
        'jenis_pegawai',
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }

    /**
     * Relasi ke User
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relasi ke PengajuanCuti
     */
    public function pengajuanCuti()
    {
        return $this->hasMany(PengajuanCuti::class, 'karyawan_id', 'id');
    }

    /**
     * Relasi ke Absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id', 'id');
    }

    /**
     * Relasi ke device absensi
     */
        public function deviceAbsensi()
    {
        return $this->hasMany(DeviceAbsensi::class);
    }

    /**
     * Get active devices
     */
    public function activeDevices()
    {
        return $this->hasMany(DeviceAbsensi::class)->where('is_active', true);
    }

    /**
     * Get jenis pegawai dari user role
     */
    public function getJenisPegawaiFromRoleAttribute()
    {
        $role = $this->user?->roles?->first();

        if ($role) {
            $roleMap = [
                'guru' => 'guru',
                'tenaga-kependidikan' => 'tenaga_kependidikan',
                'shadow-teacher' => 'shadow-teacher',
            ];

            return $roleMap[$role->name] ?? 'umum';
        }

        return $this->jenis_pegawai ?? 'umum';
    }

    /**
     * Get jabatan dari user role
     */
    public function getJabatanFromRoleAttribute()
    {
        return $this->user?->roles?->first()?->name ?? $this->jabatan ?? '-';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
