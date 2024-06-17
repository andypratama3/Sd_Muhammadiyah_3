<?php

namespace App\Models;

use App\Models\LikeArtikel;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Artikel extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'artikels';
    protected $fillable = [
        'name',
        'artikel',
        'image',
        'jumlah_klik',
        'user_id',
        'status',
        'slug',
    ];
    // category for articles
    public function categorys()
    {
        return $this->belongsToMany(Category::class, 'artikel_categorys');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_artikels');
    }
    public function likes()
    {
        return $this->hasMany(LikeArtikel::class);
    }
    //count when article click
    public function incrementClickCount()
    {
        $this->jumlah_klik++;
        $this->save();
    }
}
