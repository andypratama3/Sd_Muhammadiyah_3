<?php

namespace App\Actions\Dashboard\Task;

class DeleteTaskAction
{
    public function execute($task)
    {
        $task->permissions()->delete();
        $task->delete();
    }
}
