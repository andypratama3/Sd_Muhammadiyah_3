<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\TaskRequest;
use App\Actions\Dashboard\Task\TaskActionStore;
use App\Actions\Dashboard\Task\DeleteTaskAction;
use App\Actions\Dashboard\Task\TaskActionUpdate;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $limit = 15;
        $tasks = Task::select(['name', 'slug'])->orderBy('name')->paginate($limit);
        $count = $tasks->count();
        $no = $limit * ($tasks->currentPage() - 1);

        return view('dashboard.pengaturan.task.index', compact(
            'tasks',
            'count',
            'no'
        ));
    }

    public function create()
    {
        return view('dashboard.pengaturan.task.create');
    }

    public function store(TaskRequest $request, TaskActionStore $TaskActionStore)
    {
        $TaskActionStore->execute($request);

        return redirect()->route('dashboard.pengaturan.task.index')->with('status', 'Berhasil Menambahkan Task');
    }
    public function show(Task $task)
    {
        return view('dashboard.pengaturan.task.show', compact('task'));
    }

    public function edit(Task $task)
    {
        return view('dashboard.pengaturan.task.edit', compact('task'));
    }

    public function update(TaskRequest $request, TaskActionUpdate $taskActionUpdate, Task $slug)
    {
        $taskActionUpdate->execute($request, $slug);
        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Update');
    }

    public function destroy(DeleteTaskAction $deletTaskAction, Task $task)
    {
        $deletTaskAction->execute($task);
        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Hapus');
    }
}
