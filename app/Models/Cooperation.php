<?php

namespace App\Models;

use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cooperation extends Model
{
    use HasFactory, HasUuids, NameHasSlug;

    protected $table = 'cooperations';

    protected $fillable = [
       'name',
       'foto',
       'order',
       'slug',
    ];


}
