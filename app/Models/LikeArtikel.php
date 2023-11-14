<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LikeArtikel extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $table = 'like_artikels';
    protected $fillable = [
        'comment_id',
        'user_id',
    ];

}
