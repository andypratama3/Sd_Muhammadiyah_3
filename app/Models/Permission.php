<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Permission extends Model
{
    use \App\Http\Traits\UsesUuid;
    use SoftDeletes;

    protected $table = 'permissions';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'slug',
        'task_id',
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_permissions');
    }
}
