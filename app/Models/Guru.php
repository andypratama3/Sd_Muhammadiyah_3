<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Model
{
    use UsesUuid;
    use HasFactory;
    protected $table = 'gurus';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description',
        'lulusan',
        'foto'
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }
    public function pelajarans()
    {
        return $this->belongsToMany(Pelajaran::class, 'guru_matapelajaran');
    }
}
