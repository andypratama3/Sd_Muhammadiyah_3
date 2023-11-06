<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Actions\CommentAction;
use App\Actions\CommentActionDelete;
use App\DataTransferObjects\CommentData;

class CommentArtikelController extends Controller
{

    public function store(CommentData $commentData, CommentAction $action)
    {
        $result = $action->execute($commentData);
        if($result){
            return response()->json(['success' => 'Berhasil Menambahkana Komentar']);
        }else{
            return response()->json(['failure' => 'Gagal Menambahkana Komentar']);
        }
    }
    public function update(CommentData $commentData, CommentAction $action)
    {

        $result = $action->execute($commentData);
        if($result){
            return response()->json(['success' => 'Berhasil Menambahkana Komentar']);
        }else{
            return response()->json(['failure' => 'Gagal Menambahkana Komentar']);
        }
    }
    public function destroy(CommentActionDelete $commentActionDelete, $slug)
    {
        $result = $commentActionDelete->execute($slug);
        if($result){
            return response()->json(['success' => 'Berhasil Menghapus Komentar']);
        }else{
            return response()->json(['failure' => 'Gagal Menghapus Komentar']);
        }
    }
    public function like()
    {

    }

}
