<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Task extends Model
{
    use \App\Http\Traits\UsesUuid;
    use SoftDeletes;

    protected $table = 'tasks';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected $dates = ['deleted_at'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'task_id')->orderBy('name');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
