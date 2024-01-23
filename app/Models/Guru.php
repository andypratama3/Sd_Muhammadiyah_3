<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Model
{
    use UsesUuid;
    use HasFactory;
    protected $table = 'gurus';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'karyawan_id',
        'description',
        'lulusan',
        'foto',
        'slug'
    ];

    protected $dates = ['deleted_at'];

    public function karyawan(): hasOne
    {
        return $this->hasOne(Karyawan::class, 'id', 'karyawan_id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }
    public function pelajarans()
    {
        return $this->belongsToMany(Pelajaran::class, 'guru_matapelajaran');
    }
    //take value slug in model
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
