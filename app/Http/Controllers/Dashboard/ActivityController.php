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
        $activitys = Activity::orderBy('created_at')->take(5)->get();
        $activitys_count = $activitys->count();
        if($activitys){
            return response()->json([
                'success' => true,
                'activitys' => $activitys,
                'activitys_count' => $activitys_count
            ]);
        }else{
            return response(['error' => 'Gagal Mengambil Data']);
        }

    }
}
