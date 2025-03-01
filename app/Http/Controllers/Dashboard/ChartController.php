<?php
namespace App\Http\Controllers\Dashboard;

use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ChartController extends Controller
{
    public function chargeCount(Request $request)
    {
        // Ambil tanggal atau gunakan default bulan ini
        $chargeCountMountDate = $request->input('chargeCountMount_date');
        $chargeCountMountDate = $chargeCountMountDate ? Carbon::parse($chargeCountMountDate)->format('Y-m') : Carbon::now()->format('Y-m');

        // Ambil kategori jika ada
        $categoryPayment = $request->input('category');

        // Query total transaksi berdasarkan status
        $query = Charge::selectRaw("
            COALESCE(SUM(CASE WHEN transaction_status = 'settlement' THEN gross_amount ELSE 0 END), 0) as settlement_amount,
            COALESCE(SUM(CASE WHEN transaction_status = 'capture' THEN gross_amount ELSE 0 END), 0) as capture_amount,
            COALESCE(SUM(CASE WHEN transaction_status = 'pay_offline' THEN gross_amount ELSE 0 END), 0) as pay_offline_amount,
            COALESCE(SUM(CASE WHEN transaction_status = 'pending' THEN gross_amount ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(CASE WHEN transaction_status = 'deny' THEN gross_amount ELSE 0 END), 0) as deny_amount,
            COALESCE(SUM(CASE WHEN transaction_status = 'failed' THEN gross_amount ELSE 0 END), 0) as failed_amount
        ");

        // Filter berdasarkan tanggal
        $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$chargeCountMountDate]);

        // Filter berdasarkan kategori jika ada
        if (!empty($categoryPayment)) {
            $query->where('category_payment_id', $categoryPayment);
        }

        // Eksekusi query
        $chargeData = $query->first();

        return response()->json($chargeData);
    }
}
