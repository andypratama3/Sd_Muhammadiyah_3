<?php

namespace App\Actions;

use App\Models\Comment;

class CommentActionDelete
{
    public function execute($slug)
    {
        $comment = Comment::where('slug', $slug)->firstOrFail();
        $comment->delete();
    }
}
