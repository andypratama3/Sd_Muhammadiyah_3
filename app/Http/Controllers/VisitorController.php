<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorController extends Controller
{
    public function getVisitorData(Request $request)
    {
        $range = $request->input('range', 'month'); // Default to 'month'
        $date = Carbon::now();
        $visitors = [];

        if ($range === 'day') {
            $visitors = Visitor::whereDate('created_at', $date->format('Y-m-d'))->count();
            // \Log::info('Day data:', ['visitors' => $visitors]);
        } elseif ($range === 'month') {
            $visitors = Visitor::selectRaw('DAY(created_at) as day')
                ->selectRaw('COUNT(*) as total')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->groupBy('day')
                ->orderBy('day')
                ->get();
            // \Log::info('Month data:', $visitors->toArray());
        } elseif ($range === 'year') {
            $visitors = Visitor::selectRaw('MONTH(created_at) as month')
                ->selectRaw('COUNT(*) as total')
                ->whereYear('created_at', $date->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            // \Log::info('Year data:', $visitors->toArray());
        }

        return response()->json($visitors);
    }

}
