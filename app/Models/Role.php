<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    use HasFactory;
    use \App\Http\Traits\UsesUuid;
    use HasFactory;
    protected $table = 'roles';
    protected $guarded = ['id'];
    protected $fillable = [
    'role',
    'slug'

    ];

    protected $dates = [
        'deleted_at'
    ];

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;
        $this->attributes['slug'] = Str::slug($value). "-" .Str::random(4);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_roles');
    }
}
