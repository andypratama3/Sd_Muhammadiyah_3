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
        return redirect()->route('artikel.show')->with('success','Berhasil Menambahkan Komentar!');
    }
    public function destroy()
    {

    }
}
