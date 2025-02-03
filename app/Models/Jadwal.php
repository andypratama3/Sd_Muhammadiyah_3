<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $table = 'jadwals';
    protected $fillable = [
        'tahun_ajaran',
        'jadwal',
        'kelas',
        'category_kelas',
        'slug',
    ];
    public function kelas_jadwal()
    {
        return $this->belongsTo(Kelas::class,'kelas', 'id');
    }

    public function setTahunAjaranAttribute($value)
    {
        $this->attributes['tahun_ajaran'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }


}
