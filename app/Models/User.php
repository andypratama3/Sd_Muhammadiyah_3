<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Fortify\TwoFactorAuthenticatable;
// use Laravel\Jetstream\HasProfilePhoto;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Activity;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Http\Traits\HasPermissionsTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // use HasApiTokens;
    use HasFactory;



    // use HasProfilePhoto;
    // use Notifiable;
    // use TwoFactorAuthenticatable;
    use UsesUuid;
    use NameHasSlug;
    use HasPermissionsTrait;
    use SoftDeletes;
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'avatar',
        'nisn',
        'slug',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_change_at' => 'datetime',
    ];

    // /**
    //  * The accessors to append to the model's array form.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    protected $dates = ['deleted_at'];

    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_users');
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class,'causer_id','id');
    }
}
