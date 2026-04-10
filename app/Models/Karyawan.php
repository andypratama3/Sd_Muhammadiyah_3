<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Karyawan extends Model
{
    use UsesUuid;
    use SoftDeletes;
    use HasFactory;

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
     * Get active devices
     */
    public function activeDevices()
    {
        return $this->hasMany(DeviceAbsensi::class)->where('is_active', true);
    }

    public function devices()
    {
        return $this->hasMany(DeviceAbsensi::class);
    }

    /**
     * Get jenis pegawai dari user role
     */
    public function getJenisPegawaiFromRoleAttribute(): string
    {
        $role = $this->user?->roles?->first();

        if (!$role) return $this->jenis_pegawai ?? '-';

        $roleMap = [
            'guru'               => 'Guru',
            'tenaga-pendidikan'  => 'Tenaga Pendidik',
            'shadow-teacher'     => 'Shadow Teacher',
            'admin'              => 'Admin',
            'superadmin'         => 'Super Admin',
            'umum'               => 'Umum',
        ];

        // Label tampilan untuk UI
        return $roleMap[$role->name] ?? $role->name;
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
