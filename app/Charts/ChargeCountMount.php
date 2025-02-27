<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Charge;
use Carbon\Carbon;

class ChargeCountMount
{
    protected $chart;
    protected $chargeCountMountDate;
    protected $category;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
        $this->chargeCountMountDate = Carbon::now()->format('Y-m');
    }

    public function setChargeCountMountDate($date)
    {
        $this->chargeCountMountDate = $date;
        return $this;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {
        $query = Charge::selectRaw("
            SUM(CASE WHEN transaction_status = 'settlement' THEN gross_amount ELSE 0 END) as settlement_amount,
            SUM(CASE WHEN transaction_status = 'pay_offline' THEN gross_amount ELSE 0 END) as pay_offline_amount,
            SUM(CASE WHEN transaction_status = 'pending' THEN gross_amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN transaction_status = 'deny' THEN gross_amount ELSE 0 END) as deny_amount,
            SUM(CASE WHEN transaction_status = 'failed' THEN gross_amount ELSE 0 END) as failed_amount
        ");

        if (!empty($this->chargeCountMountDate)) {
            $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$this->chargeCountMountDate]);
        }

        if (!empty($this->category)) {
            $query->where('category_payment_id', $this->category);
        }

        $chargeData = $query->first();

        if (!$chargeData || ($chargeData->settlement_amount == 0 &&
            $chargeData->pay_offline_amount == 0 &&
            $chargeData->pending_amount == 0 &&
            $chargeData->deny_amount == 0 &&
            $chargeData->failed_amount == 0)) {
            return $this->chart->pieChart()
                ->setTitle("Total Pembayaran - Bulan {$this->chargeCountMountDate}")
                ->addData([100])
                ->setLabels(['No Data']);
        }

        return $this->chart->pieChart()
            ->setTitle("Total Pembayaran - Bulan " . Carbon::parse($this->chargeCountMountDate)->format('F Y'))
            ->addData([
                (float) $chargeData->settlement_amount,
                (float) $chargeData->pay_offline_amount,
                (float) $chargeData->pending_amount,
                (float) ($chargeData->deny_amount + $chargeData->failed_amount),
            ])
            ->setLabels([
                'Settlement: Rp ' . number_format($chargeData->settlement_amount, 0, ',', '.'),
                'Pay Offline: Rp ' . number_format($chargeData->pay_offline_amount, 0, ',', '.'),
                'Pending: Rp ' . number_format($chargeData->pending_amount, 0, ',', '.'),
                'Deny & Failed: Rp ' . number_format($chargeData->deny_amount + $chargeData->failed_amount, 0, ',', '.'),
            ]);
    }
}
