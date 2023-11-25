<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Models\CategoryKelas;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use SoftDeletes;
    protected $tables = 'kelas';

    protected $fillable = [
        'name',
        'category_kelas',
        'slug',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user_table', 'user_id', 'role_id');
    }

    public function categoryKelas()
    {
        return $this->belongsToMany(CategoryKelas::class, 'kelas_category');
    }



}
