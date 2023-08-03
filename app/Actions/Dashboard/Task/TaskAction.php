<?php

namespace App\Actions\Dashboard\Task;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskAction
{
    public function execute(Request $request, $task)
    {
        return Task::updateOrCreate(
            ['slug' => $task->slug],
            [
                'name' => $request->name,
                'description' => $request->description,
            ]
        );
    }
}
