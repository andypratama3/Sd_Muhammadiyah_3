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
    public function categorys()
    {
        return $this->belongsToMany(Category::class, 'artikel_categorys');
    }

    public function comments()
    {
        return $this->belongsToMany(Comment::class, 'comments_users');
    }

    public function incrementClickCount()
    {
        $this->jumlah_klik++;
        $this->save();
    }
}
