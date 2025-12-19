<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Berita;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BeritaDataController extends Controller
{
    public function list()
    {
        $data = Berita::orderBy('created_at', 'desc')->paginate(10);
        return $this->paginated($data);
    }
}
