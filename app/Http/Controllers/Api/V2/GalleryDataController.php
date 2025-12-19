<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GalleryDataController extends Controller
{
    public function listGallery()
    {
        $gallery = Gallery::orderBy('created_at', 'desc')->get();

        if($gallery) {
            return response()->json([
                'status' => 'success',
                'data' => $gallery,
            ], 200);
        }
    }
}
