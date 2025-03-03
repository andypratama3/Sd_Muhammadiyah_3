<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function __construct()
    {
        // $this->middleware('role:superadmin');
    }
    public function index()
    {
        $limit = 15;
        $activitys = Activity::orderBy('created_at')->paginate($limit);
        $count = $activitys->count();
        $no = $limit * ($activitys->currentPage() - 1);
        return view('dashboard.data.activity.index', compact('activitys','count','no'));
    }
    public function activitys()
    {
        $activities = Activity::orderBy('created_at','desc')->get();
        return response()->json([
            "success" => true,
            "activitys" => $activities,
            "activitys_count" => $activities->count()
        ]);

    }
}
