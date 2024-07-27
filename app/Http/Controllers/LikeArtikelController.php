<?php

namespace App\Http\Controllers;

use App\Models\LikeArtikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeArtikelController extends Controller
{
    public function like(Request $request)
    {
        
        if(Auth::check()){
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
        }else{
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu');
        }

    }

}
