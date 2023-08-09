<?php

namespace App\Actions\Dashboard\Task;


use Str;
use App\Models\Task;
use App\Models\Permission;
use Illuminate\Http\Request;

class TaskActionUpdate
{
    public function execute(Request $request,Task $slug): void
    {

        $name = $request->input('name');
        $description = $request->input('description');
        $slug = Str::slug($name);

        $task = Task::where('slug', $slug)->firstOrFail();
        $task->name = $name;
        $task->description = $description;
        $task->slug = $slug;
        $task->save();
        $permissions = Permission::where('task_id', $task->id)->get();

        foreach ($permissions as $key => $permission) {
            $perm = Permission::find($permission->id);
            $perm->name = \explode(' ', $perm->name)[0] . " " . $name;
            $perm->slug = \explode('-', $perm->slug)[0] . "-" . $slug;
            $perm->save();
        }
    }
}
