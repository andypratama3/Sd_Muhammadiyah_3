<?php

namespace App\Exports;

use App\Models\Charge;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ChargeExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $kelas;

    public function __construct($startDate, $endDate, $kelas = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->kelas = $kelas;
    }

    public function query()
    {
        // Query charges with related siswa and filter by date range
        $charges = Charge::with('siswa') // Eager load the siswa relationship
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Filter by class (kelas) if provided
        if ($this->kelas) {
            $charges->whereHas('siswa.kelas', function ($query) {
                $query->where('id', $this->kelas);
            });
        }

        return $charges->select(
            'order_id',
            'siswa_id',
            'gross_amount as total_bayar',
            'payment_type as jenis_bayar',
            'bank',
            'va_number',
            'transaction_id as id_transaksi',
            'created_at',
            'transaction_status as status'
        );
    }

    // Mapping the data to match your required format
    public function map($charge): array
    {
        return [
            $charge->order_id,
            $charge->siswa->name ?? 'N/A', // Accessing siswa name via relationship
            "Rp " . number_format($charge->total_bayar, 2, ',', '.'),
            $charge->jenis_bayar,
            $charge->bank ?? 'N/A',
            $charge->va_number ?? 'N/A',
            $charge->id_transaksi,
            Carbon::parse($charge->created_at)->format('d-m-Y H:i'),
            $charge->status,
        ];
    }

    // Define the Excel headings
    public function headings(): array
    {
        return [
            "Order ID",
            "Siswa",
            "Total Bayar",
            "Jenis Bayar",
            "Bank",
            "Nomor VA",
            "ID Transaksi",
            "Tanggal Transaksi",
            "Status Transaksi",
        ];
    }
}
