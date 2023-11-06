<?php

namespace App\Models;

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
        'slug',
    ];
    // category for articles
    public function categorys()
    {
        return $this->belongsToMany(Category::class, 'artikel_categorys');
    }

    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_artikels');
    }
    //like for artikel
    public function like(){
        return $this->belongsToMany(User::class, 'artikel_likes');
    }
    //count when article click
    public function incrementClickCount()
    {
        $this->jumlah_klik++;
        $this->save();
    }
}
