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
        'user_id',
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
