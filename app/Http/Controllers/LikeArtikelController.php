<?php

namespace App\Http\Controllers;

use App\Models\LikeArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeArtikelController extends Controller
{
    public function like(Request $request)
{
    $likeData = [
        'comment_id' => $request->comment_id,
        'user_id' => Auth::id(),
    ];

    $existingLike = LikeArtikel::where($likeData)->first();

    if ($existingLike) {
        $existingLike->delete();
    } else {
        LikeArtikel::create($likeData);
    }

    return null;
}

}
