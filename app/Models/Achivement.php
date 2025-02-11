<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Achivement extends Model
{
    use HasFactory, UsesUuid, NameHasSlug;

    protected $table = 'achivements';

    protected $fillable = [
        'name',
        'foto',
        'order',
        'slug',
    ];
}
