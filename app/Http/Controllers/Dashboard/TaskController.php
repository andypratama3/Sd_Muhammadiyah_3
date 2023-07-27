<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Dashboard\Task\DeleteTaskAction;
use App\Actions\Dashboard\Task\TaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\TaskRequest;
use App\Models\Task;

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

    public function store(TaskRequest $request, TaskAction $taskAction)
    {
        $taskAction->execute($request, new Task);

        return redirect()->route('dashboard.pengaturan.task.index')->with('status', 'Berhasil Menambahkan Task');
    }

    public function edit(Task $task)
    {
        return view('dashboard.pengaturan.task.show', compact('task'));
    }

    public function update(TaskRequest $request, TaskAction $taskAction, Task $task)
    {
        $taskAction->execute($request, $task);

        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Update');
    }

    public function destroy(DeleteTaskAction $deletTaskAction, Task $task)
    {
        $deletTaskAction->execute($task);

        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Hapus');
    }
}
