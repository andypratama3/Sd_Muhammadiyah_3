<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Dashboard\Task\TaskAction;
use App\Http\Requests\Dashboard\TaskRequest;

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
        $no = $limit * ($tasks->currentPage() - 1 );
        return view('dashboard.pengguna.task.index', compact(
            'tasks',
            'count',
            'no'
        ));
    }
    public function create()
    {
        return view('dashboard.pengguna.task.create');
    }
    public function store(TaskRequest $request, TaskAction $taskAction )
    {
        $taskAction->execute($request, New Task);
        return redirect()->route('dashboard.pengaturan.task.index')->with('status','Berhasil Menambahkan Task');
    }
    public function show($task)
    {
        return view('dashboard.pengaturan.task.show', compact('task'));
    }
    public function edit($task)
    {

        return view('dashboard.pengaturan.task.edit', compact('task'));
    }
    public function update(TaskRequest $request, TaskAction $taskAction , Task $task)
    {
        $taskAction->execute($request, $task);
        return redirect()->route('dashboard.kategori.index')->with('success','Kategori Berhasil Di Update');
    }

}
