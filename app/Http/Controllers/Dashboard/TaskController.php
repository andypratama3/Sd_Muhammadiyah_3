<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Task;
use App\Models\Permission;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\TaskData;
use Illuminate\Http\Request;
use App\Actions\Dashboard\Task\TaskAction;
use App\Http\Requests\Dashboard\TaskRequest;
use App\Actions\Dashboard\Task\DeleteTaskAction;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }

    public function index(Request $request)
    {
        $limit = 15;
        $query = Task::select(['name', 'slug'])->orderBy('name');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->paginate($limit);

        $count = $tasks->total();
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

    public function store(TaskAction $TaskAction ,TaskData $taskdata)
    {
        $TaskAction->execute($taskdata);

        return redirect()->route('dashboard.pengaturan.task.index')->with('status', 'Berhasil Menambahkan Task');
    }
    public function show(Task $task)
    {
        return view('dashboard.pengaturan.task.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $array = $task->permissions->pluck('id')->toArray();
        $view = Permission::where('name', 'View '.$task->name)->first();
        $create = Permission::where('name', 'Create '.$task->name)->first();
        $edit = Permission::where('name', 'Edit '.$task->name)->first();
        $delete = Permission::where('name', 'Delete '.$task->name)->first();
        $count = count($array);
        return view('dashboard.pengaturan.task.edit', compact('task','array','view','create','edit','delete','count'));
    }

    public function update(TaskAction $TaskAction, TaskData $taskdata)
    {
        $TaskAction->execute($taskdata);
        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Update');
    }

    public function destroy(DeleteTaskAction $deletTaskAction, Task $task)
    {
        $deletTaskAction->execute($task);
        return redirect()->route('dashboard.pengaturan.task.index')->with('success', 'Task Berhasil Di Hapus');
    }
}
