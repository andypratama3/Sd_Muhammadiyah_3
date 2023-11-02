<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\CommentAction;
use App\DataTransferObjects\CommentData;

class CommentArtikelController extends Controller
{
    public function store(CommentData $commentData, CommentAction $action)
    {
        $action->execute($commentData);
        if($action){
            return response()->json(['success' => 'Berhasil Menambahkana Komentar']);
        }else{
            return response()->json(['gagal' => 'Berhasil Menambahkana Komentar']);
        }
    }


    public function destroy()
    {

    }
}
