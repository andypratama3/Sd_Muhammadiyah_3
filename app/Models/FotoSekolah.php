<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FotoSekolah extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'foto_sekolah';

    protected $fillable = [
        'name',
        'foto',
    ];
}
