<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Charge;

class ChargeChart
{
    protected $chart;
    protected $month;
    protected $year;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
        $this->month = date('m'); // Menyimpan bulan saat ini
        $this->year = date('Y');  // Menyimpan tahun saat ini
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function setMonth($month)
    {
        $this->month = $month;
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
            ->whereMonth('created_at', $this->month) // Filter hanya bulan berjalan
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Inisialisasi array untuk menyimpan data transaksi per bulan
        $monthlySettlementCounts = array_fill(0, 12, 0);
        $monthlyPendingCounts = array_fill(0, 12, 0);
        $monthlyDenyCounts = array_fill(0, 12, 0);

        // Populate the counts arrays dengan data dari database
        foreach ($charges as $item) {
            $index = $item->month - 1; // Sesuaikan index dengan array
            $monthlySettlementCounts[$index] = $item->settlement_count;
            $monthlyPendingCounts[$index] = $item->pending_count;
            $monthlyDenyCounts[$index] = $item->deny_count;
        }

        // Define nama bulan untuk X-axis
        $months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        return $this->chart->barChart()
            ->setTitle('Pembayaran SPP Siswa Bulan ' . $months[$this->month - 1])
            ->setSubtitle('Jumlah Transaksi pada bulan ' . $months[$this->month - 1])
            ->addData('Settlement', array_values($monthlySettlementCounts))
            ->addData('Pending', array_values($monthlyPendingCounts))
            ->addData('Deny', array_values($monthlyDenyCounts))
            ->setColors(['#5ce70b', '#f9ca24', '#e74c3c'])
            ->setXAxis($months);
    }
}


// namespace App\Charts;

// use ArielMejiaDev\LarapexCharts\LarapexChart;
// use App\Models\Charge;
// use Carbon\Carbon;

// class ChargeChart
// {
//     protected $chart;
//     protected $year;

//     public function __construct(LarapexChart $chart)
//     {
//         $this->chart = $chart;
//         $this->month = date('m');
//         $this->year = date('Y');
//     }

//     public function setYear($year)
//     {
//         $this->year = $year;
//         return $this;
//     }

//     public function build(): \ArielMejiaDev\LarapexCharts\BarChart
//     {
//         // Fetch charge data grouped by month and status for the selected year
//         $charges = Charge::selectRaw('MONTH(created_at) as month,
//                                     SUM(CASE WHEN transaction_status = "settlement" THEN 1 ELSE 0 END) as settlement_count,
//                                     SUM(CASE WHEN transaction_status = "pending" THEN 1 ELSE 0 END) as pending_count,
//                                     SUM(CASE WHEN transaction_status = "deny" THEN 1 ELSE 0 END) as deny_count')
//             ->whereYear('created_at', $this->year)
//             ->groupBy('month')
//             ->orderBy('month')
//             ->get();
//         // Initialize the count for each month (1 to 12)
//         $monthlysettlementCounts = array_fill(1, 12, 0);
//         $monthlyPendingCounts = array_fill(1, 12, 0);
//         $monthlyDenyCounts = array_fill(1, 12, 0);


//         // Populate the counts arrays with actual data
//         foreach ($charges as $item) {
//             $monthlysettlementCounts[$item->month] = $item->settlement_count;
//             $monthlyPendingCounts[$item->month] = $item->pending_count;
//             $monthlyDenyCounts[$item->month] = $item->deny_count;
//         }

//         // Define month names for the X-axis
//         $months = [
//             'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
//             'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
//         ];

//         return $this->chart->barChart()
//             ->setTitle('Pembayaran SPP Siswa Chart')
//             ->setSubtitle('Jumlah Transaksi per Bulan')
//             ->addData('settlement', array_values($monthlysettlementCounts))
//             ->addData('Pending', array_values($monthlyPendingCounts))
//             ->addData('Deny', array_values($monthlyDenyCounts))
//             ->setColors(['#5ce70b', '#f9ca24', '#e74c3c'])
//             ->setXAxis($months);
//     }
// }
