<?php

namespace App\Actions;

use App\Models\Comment;
use App\DataTransferObjects\CommentData;

class CommentAction
{
    public function execute(CommentData $commentData)
    {
        $comment = Comment::updateOrCreate(
            ['slug' => $commentData->slug],
            [
                'comment'=> $commentData->comment,
            ]);

        if(empty($commentData->slug))
        {
            $comment->artikels()->attach($commentData->artikel);
            $comment->users()->attach($commentData->user);
        }else{
            $comment->artikel()->sync($commentData->artikel);
            $comment->users()->sync($commentData->user);
        }
        return $comment;
    }
}
