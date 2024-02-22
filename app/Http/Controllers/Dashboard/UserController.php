<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }
    public function index()
    {
        $limit = 15;
        $users = User::select(['name','email','slug'])->paginate($limit);
        $no = $limit * ($users->currentPage() - 1);
        return view('dashboard.pengaturan.user.index', compact('users','no'));
    }
    public function store(UserRequest $request, UserAction $userAction)
    {
        $userAction->execute($request, new User);
        return redirect()->route('dashboard.pengaturan.user.index')->with('success','Berhasil Menambahkan User!');
    }
    public function show(User $user)
    {
        return view('dashboard.pengaturan.user.show', compact('user'));
    }
    public function update()
    {

    }
    public function destroy(DeleteUserAction $deleteUserAction, User $user )
    {
        $deleteUserAction->execute($user);

    }

}
