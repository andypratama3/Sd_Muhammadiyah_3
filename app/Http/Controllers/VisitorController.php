<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorController extends Controller
{
    public function getVisitorData(Request $request)
    {
        try {
            $range = $request->input('range', 'month');
            $date  = Carbon::now();

            if ($range === 'day') {
                return response()->json([
                    'total' => Visitor::whereDate('created_at', $date->toDateString())->count()
                ]);
            }

            if ($range === 'month') {
                return response()->json(
                    Visitor::selectRaw('DAY(created_at) as day, COUNT(*) as total')
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->groupByRaw('DAY(created_at)')
                        ->orderBy('day')
                        ->get()
                );
            }

            if ($range === 'year') {
                return response()->json(
                    Visitor::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                        ->whereYear('created_at', $date->year)
                        ->groupByRaw('MONTH(created_at)')
                        ->orderBy('month')
                        ->get()
                );
            }

            return response()->json([], 200);

        } catch (\Throwable $e) {
            \Log::error('VisitorController error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Internal Server Error'
            ], 500);
        }
    }
}