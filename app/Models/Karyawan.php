<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Karyawan extends Model
{
    use \App\Http\Traits\UsesUuid;
    use SoftDeletes;

    protected $table = 'karyawans';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'sex',
        'birth_date',
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
}
