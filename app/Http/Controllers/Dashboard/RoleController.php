<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Role;
use App\Models\Task;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{

    public function __contstruct()
    {
        $this->middleware('permission:view-roles', ['only' => ['index']]);
        $this->middleware('permission:create-roles', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-roles', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-roles', ['only' => ['delete']]);
    }
    public function index()
    {
        $limit = 15;
        $roles = Role::select(['name', 'slug'])->where('slug', '!=', 'superadmin')->orderBy('name')->paginate($limit);
        $count = $roles->count();
        $no = $limit * ($roles->currentPage() - 1);

        return view('dashboard.pengaturan.role.index', compact(
            'roles',
            'count',
            'no'
        ));
    }

    public function create()
    {
        $tasks = Task::orderBy('slug')->get();
        return view('dashboard.pengaturan.role.create', compact('tasks'));
    }

    public function edit(Role $role)
    {
        return view('dashboard.pengaturan.role.show', compact('role'));
    }
}
