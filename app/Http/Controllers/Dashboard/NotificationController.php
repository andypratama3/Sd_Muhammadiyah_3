<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function index()
    {
        return view('dashboard.notification.index');
    }

    public function data_table()
    {
        $notifications = Activity::orderBy('created_at', 'desc');

        return DataTables::of($notifications)
            ->addColumn('created_at', function ($row) {
                return $row->created_at->diffForHumans();
            })
            ->addIndexColumn()
            ->make(true);
    }
}
