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
    public function store()
    {

    }
    public function show()
    {

    }
    public function update()
    {

    }
    public function destroy()
    {

    }

}
