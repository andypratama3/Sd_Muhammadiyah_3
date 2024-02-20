<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activitys = Activity::orderBy('created_at');
        return view('dashboard.data.activity.index', compact('activitys'));
    }
}
