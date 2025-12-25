<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ViewsDataController extends Controller
{
    /**
     * Get visitor statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewData()
    {
        // Menggunakan scope yang sudah dibuat di Model
        $visitor_by_day = Visitor::today()->count();
        $visitor_by_month = Visitor::thisMonth()->count();
        $visitor_by_year = Visitor::thisYear()->count();

        return $this->success([
            'visitor_by_day' => $visitor_by_day,
            'visitor_by_month' => $visitor_by_month,
            'visitor_by_year' => $visitor_by_year,
            'last_updated' => now()->toIso8601String(),
        ], 'Visitor statistics retrieved successfully');
    }

    /**
     * Store visitor log
     * Only logs once per day per IP address
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $isNewVisitor = Visitor::logOncePerDay();

            // Get current stats after logging
            $stats = [
                'visitor_by_day' => Visitor::today()->count(),
                'is_new_visitor' => $isNewVisitor,
            ];

            return $this->success(
                $stats,
                $isNewVisitor ? 'Visitor logged successfully' : 'Visitor already logged today'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to log visitor: ' . $e->getMessage());

            return $this->error(
                'Failed to log visitor',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get visitor history with pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $date = $request->get('date'); // Optional filter by date

        $query = Visitor::query()->orderBy('date', 'desc');

        if ($date) {
            $query->byDate($date);
        }

        $visitors = $query->paginate($perPage);

        return $this->success($visitors, 'Visitor history retrieved successfully');
    }

    /**
     * Get visitor statistics by date range
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statsByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $visitors = Visitor::dateRange($startDate, $endDate)
            ->selectRaw('DATE(date) as visit_date, COUNT(*) as total')
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'asc')
            ->get();

        $total = Visitor::dateRange($startDate, $endDate)->count();

        return $this->success([
            'data' => $visitors,
            'total' => $total,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], 'Statistics retrieved successfully');
    }
}
