<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Charge;

class ChargeCountMount
{
    protected $chart;
    protected $month;
    protected $year;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
        $this->month = date('m');
        $this->year = date('Y');
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

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {
        // Mengambil total gross_amount berdasarkan status transaksi
        $chargeData = Charge::selectRaw('
                SUM(CASE WHEN transaction_status = "settlement" THEN gross_amount ELSE 0 END) as settlement_amount,
                SUM(CASE WHEN transaction_status = "pay_offline" THEN gross_amount ELSE 0 END) as pay_offline_amount,
                SUM(CASE WHEN transaction_status = "pending" THEN gross_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN transaction_status = "deny" THEN gross_amount ELSE 0 END) as deny_amount,
                SUM(CASE WHEN transaction_status = "failed" THEN gross_amount ELSE 0 END) as failed_count
            ')
            ->whereYear('created_at', $this->year)
            ->whereMonth('created_at', $this->month)
            ->first();

        return $this->chart->pieChart()
            ->setWidth(500)
            ->setHeight(500)
            ->setTitle("Total Gross Amount - Bulan {$this->month}, {$this->year}")
            ->addData([
                (float) ($chargeData->settlement_amount ?? 0),
                (float) ($chargeData->pay_offline_amount ?? 0),
                (float) ($chargeData->pending_amount ?? 0),
                (float) ($chargeData->deny_amount + $chargeData->failed_count ?? 0),
            ])
            ->setLabels(['Settlement', 'Pay Offline', 'Pending', 'Deny']);
    }
}
