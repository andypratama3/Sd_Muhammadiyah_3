<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $table = 'comments';
    protected $fillable = [
        'comment',
        'slug',
    ];
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value) . "-" . Str::random(4);
    }

    //users comment artikel
    public function users()
    {
        return $this->belongsToMany(User::class, 'comments_users');
    }
    //comment artikel
    public function artikels()
    {
        return $this->belongsToMany(Artikel::class, 'comments_artikels');
    }
    // like comment
    public function likes()
    {
        return $this->hasOne(LikeArtikel::class)->where('like_artikels.user_id', Auth::user()->id);
    }
    public function countLike()
    {
        return $this->hasOne(LikeArtikel::class)->count();
    }

}
