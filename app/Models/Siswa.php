<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;
    protected $tables = 'siswas';
    protected $fillable = [
        'name',
        'nisn',
        'alamat',
        'no_telp',

    ];
}
