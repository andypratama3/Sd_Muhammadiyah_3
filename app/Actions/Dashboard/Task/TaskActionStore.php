<?php

namespace App\Actions\Dashboard\Task;

use App\Models\Task;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TaskActionStore
{
    public function execute(Request $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');
        $slug = Str::slug($name);

        $task = new Task();
        $task->name = $name;
        $task->description = $description;
        $task->slug = $slug;
        $task->save();

        $data = array(
            [
                'name' => 'View ' . $name,
                'slug' => 'view-' . $slug,
                'task_id' => $task->id
            ],
            [
                'name' => 'Create ' . $name,
                'slug' => 'create-' . $slug,
                'task_id' => $task->id
            ],
            [
                'name' => 'Edit ' . $name,
                'slug' => 'edit-' . $slug,
                'task_id' => $task->id
            ],
            [
                'name' => 'Delete ' . $name,
                'slug' => 'delete-' . $slug,
                'task_id' => $task->id
            ],
        );

        foreach ($data as $item) {
            Permission::Create($item);
        }
    }
}
