<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $table = 'jadwals';
    protected $fillable = [
        'tahun_ajaran',
        'jadwal',
        'kelas_id',
        'category_kelas',
        'slug',
    ];


    public function kelas_jadwal()
    {
        return $this->belongsTo(Kelas::class,'kelas_id', 'id');
    }

    public function jadwal_details()
    {
        return $this->hasMany(JadwalDetail::class);
    }

    public function setTahunAjaranAttribute($value)
    {
        $this->attributes['tahun_ajaran'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }


}
