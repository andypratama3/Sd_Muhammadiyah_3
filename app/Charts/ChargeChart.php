<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Charge;
use Carbon\Carbon;

class ChargeChart
{
    protected $chart;
    protected $year;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
        $this->year = date('Y'); // Default year is the current year
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
        // Fetch charge data grouped by month and status for the selected year
        $charges = Charge::selectRaw('MONTH(created_at) as month,
                                    SUM(CASE WHEN transaction_status = "settlement" THEN 1 ELSE 0 END) as settlement_count,
                                    SUM(CASE WHEN transaction_status = "pending" THEN 1 ELSE 0 END) as pending_count,
                                    SUM(CASE WHEN transaction_status = "deny" THEN 1 ELSE 0 END) as deny_count')
            ->whereYear('created_at', $this->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        // Initialize the count for each month (1 to 12)
        $monthlysettlementCounts = array_fill(1, 12, 0);
        $monthlyPendingCounts = array_fill(1, 12, 0);
        $monthlyDenyCounts = array_fill(1, 12, 0);


        // Populate the counts arrays with actual data
        foreach ($charges as $item) {
            $monthlysettlementCounts[$item->month] = $item->settlement_count;
            $monthlyPendingCounts[$item->month] = $item->pending_count;
            $monthlyDenyCounts[$item->month] = $item->deny_count;
        }

        // Define month names for the X-axis
        $months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        return $this->chart->barChart()
            ->setTitle('Pembayaran SPP Siswa Chart')
            ->setSubtitle('Jumlah Transaksi per Bulan')
            ->addData('settlement', array_values($monthlysettlementCounts))
            ->addData('Pending', array_values($monthlyPendingCounts))
            ->addData('Deny', array_values($monthlyDenyCounts))
            ->setColors(['#5ce70b', '#f9ca24', '#e74c3c'])
            ->setXAxis($months);
    }
}
