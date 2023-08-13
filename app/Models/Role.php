<?php

namespace App\Models;

use Str;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use \App\Http\Traits\UsesUuid;
    use UsesUuid;
    use NameHasSlug;
    use HasPermissionsTrait;
    use SoftDeletes;

    protected $table = 'roles';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_roles');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
