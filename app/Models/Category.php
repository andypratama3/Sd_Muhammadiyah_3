<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'categorys';

    protected $fillable = [
        'name',
        'slug',
    ];
    //category artikel
    public function artikels()
    {
        return $this->belongsToMany(Artikel::class, 'artikel_categorys');
    }

}
