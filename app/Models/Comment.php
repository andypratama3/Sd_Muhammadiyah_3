<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'comments';
    protected $fillable = [
        'comment',
        'slug',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'comments_users');
    }
    public function artikels()
    {
        return $this->belongsToMany(Artikel::class, 'comments_users');
    }
}
