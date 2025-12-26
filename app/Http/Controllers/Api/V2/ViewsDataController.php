<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class ViewsDataController extends Controller
{
    /**
     * Get visitor statistics
     *
     * GET /api/v2/views
     */
    public function viewData()
    {
        try {
            // Menggunakan scope di model Visitor
            $visitor_by_day = Visitor::today()->count();
            $visitor_by_month = Visitor::thisMonth()->count();
            $visitor_by_year = Visitor::thisYear()->count();

            return $this->success([
                'visitor_by_day' => $visitor_by_day,
                'visitor_by_month' => $visitor_by_month,
                'visitor_by_year' => $visitor_by_year,
                'last_updated' => now()->toIso8601String(),
            ], 'Visitor statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError(
                'Gagal mengambil statistik visitor: ' . $e->getMessage()
            );
        }
    }

    /**
     * Store visitor log
     * Only logs once per day per IP address
     *
     * POST /api/v2/views
     */
    public function store(Request $request)
    {
        try {
            $isNewVisitor = Visitor::logOncePerDay();

            $stats = [
                'visitor_by_day' => Visitor::today()->count(),
                'is_new_visitor' => $isNewVisitor,
            ];

            return $this->success(
                $stats,
                $isNewVisitor
                    ? 'Visitor logged successfully'
                    : 'Visitor already logged today'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to log visitor: ' . $e->getMessage());

            return $this->serverError(
                'Failed to log visitor: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get visitor history with pagination
     *
     * GET /api/v2/views/history
     */
    public function history(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $date = $request->get('date'); // optional filter

            $query = Visitor::query()->orderBy('date', 'desc');

            if ($date) {
                $query->byDate($date);
            }

            $visitors = $query->paginate($perPage);

            return $this->paginated(
                $visitors,
                'Visitor history retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->serverError(
                'Gagal mengambil riwayat visitor: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get visitor statistics by date range
     *
     * GET /api/v2/views/stats-by-range
     */
    public function statsByDateRange(Request $request)
    {
        try {
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
        } catch (ValidationException $e) {
            return $this->validationError(
                'Validasi gagal',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->serverError(
                'Gagal mengambil statistik visitor: ' . $e->getMessage()
            );
        }
    }
}
