<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Role;
use App\Models\Task;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-pengaturan', ['only' => ['index']]);
        $this->middleware('permission:manage-pengaturan', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index()
    {

        $limit = 15;
        $roles = Role::select(['id', 'name'])
            ->orderBy('name')
            ->paginate($limit);

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
        $permissions = Permission::orderBy('name')->get();

        return view('dashboard.pengaturan.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'permissions.required' => 'Pilih minimal satu permission',
            'permissions.min' => 'Pilih minimal satu permission',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        // Attach permissions using id
        foreach ($validated['permissions'] as $permissionid) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionid,
                'role_id' => $role->id,
            ]);
        }

        return redirect()->route('dashboard.pengaturan.role.index')
            ->with('success', 'Role berhasil ditambahkan');
    }

    public function edit($id)
    {
        $role = Role::find($id);

        // Prevent editing superadmin role
        if ($role->name === 'superadmin') {
            abort(403, 'Tidak dapat mengedit role superadmin');
        }

        $permissions = Permission::orderBy('name')->get();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();


        return view('dashboard.pengaturan.role.edit', [
            'role' => $role,
            'permissions' => Permission::all(),
            'rolePermissions' => $role->permissions->pluck('id')->toArray(),
        ]);

    }

    public function update(Request $request,$id)
    {
        $role = Role::find($id);

        // Prevent updating superadmin role
        if ($role->name === 'superadmin') {
            abort(403, 'Tidak dapat mengubah role superadmin');
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . ',id',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'permissions.required' => 'Pilih minimal satu permission',
            'permissions.min' => 'Pilih minimal satu permission',
        ]);


        $role->update([
            'name' => $validated['name'],
        ]);

        $role->permissions()->sync($validated['permissions']);

        return redirect()->route('dashboard.pengaturan.role.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting superadmin role
        if ($role->name === 'superadmin') {
            abort(403, 'Tidak dapat menghapus role superadmin');
        }

        // Delete role permissions first
        DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->delete();

        // Delete model has roles (user assignments)
        DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->delete();

        // Delete the role
        $role->delete();

        return redirect()->route('dashboard.pengaturan.role.index')
            ->with('success', 'Role berhasil dihapus');
    }
}
