<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Charge;
use Carbon\Carbon;

class ChargeCountMount
{
    protected $chart;
    protected $setChargeCountMount_date;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
        $this->setChargeCountMount_date = Carbon::now()->format('Y-m'); // Default ke bulan ini
    }

    public function setChargeCountMount_date($setChargeCountMount_date)
    {
        $this->setChargeCountMount_date = $setChargeCountMount_date;
        return $this;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {
        // Konversi format Y-m menjadi rentang tanggal awal & akhir bulan

        // Mengambil total gross_amount berdasarkan status transaksi
        $chargeData = Charge::selectRaw('
                SUM(CASE WHEN transaction_status = "settlement" THEN gross_amount ELSE 0 END) as settlement_amount,
                SUM(CASE WHEN transaction_status = "pay_offline" THEN gross_amount ELSE 0 END) as pay_offline_amount,
                SUM(CASE WHEN transaction_status = "pending" THEN gross_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN transaction_status = "deny" THEN gross_amount ELSE 0 END) as deny_amount,
                SUM(CASE WHEN transaction_status = "failed" THEN gross_amount ELSE 0 END) as failed_amount
            ')
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", $this->setChargeCountMount_date)
            ->first();

        if($chargeData->settlement_amount == null && $chargeData->pay_offline_amount == null && $chargeData->pending_amount == null && $chargeData->deny_amount == null && $chargeData->failed_amount == null) {
            return $this->chart->pieChart()
                // ->setWidth(500)
                // ->setHeight(500)
                ->setTitle("Total Pembayaran - Bulan {$this->setChargeCountMount_date}")
                ->addData([
                    100,
                ])

                ->setLabels(['No Data']);
        }

        return $this->chart->pieChart()
            // ->setWidth(500)
            // ->setHeight(500)
            ->setTitle("Total Pembayaran - Bulan {$this->setChargeCountMount_date}")
            ->addData([
                (float) ($chargeData->settlement_amount ?? 0),
                (float) ($chargeData->pay_offline_amount ?? 0),
                (float) ($chargeData->pending_amount ?? 0),
                (float) (($chargeData->deny_amount ?? 0) + ($chargeData->failed_amount ?? 0)),
            ])
            ->setLabels(['Settlement : Rp. ' . $chargeData->settlement_amount,
                        'Pay Offline : Rp. ' . $chargeData->pay_offline_amount,
                        'Pending     : Rp. ' . $chargeData->pending_amount,
                        'Deny & Failed : Rp. ' . ($chargeData->deny_amount ?? 0) + ($chargeData->failed_amount ?? 0),
            ]);
    }
}
