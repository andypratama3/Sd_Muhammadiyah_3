<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KritikSaran extends Model
{
    use HasFactory,UsesUuid,NameHasSlug;
    protected $table = 'kritik_sarans';
    protected $fillable = ['name', 'email', 'subject', 'message','slug'];
}
