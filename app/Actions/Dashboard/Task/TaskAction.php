<?php

namespace App\Actions\Dashboard\Task;

use App\Models\Task;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TaskAction
{
    public function execute($taskData): Task
    {
        $name = $taskData->name;

        $task = Task::updateOrCreate(
            ['slug' => $taskData->slug],
            [
                'name' => $name,
                'description' => $taskData->description,
            ],
        );

        $task_id = $task->id;

        $perm = Permission::where('task_id', $task_id)->get();
        foreach ($perm as $item) {
            $item->delete();
        }

        foreach ($taskData->permissions as $key => $permissions) {
            $permission = Permission::create(
                [
                    'name' => $permissions.' '.$name,
                    'slug' => Str::slug($permissions.'-'.$name),
                    'task_id' => $task_id,
                ],
            );
        }

        return $task;
    }
}
