<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Activity;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use SoftDeletes;
    use Notifiable;
    use HasRoles;

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'nisn',
        'slug',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_change_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getMorphClass()
    {
        return self::class;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_users');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'causer_id', 'id');
    }
}
