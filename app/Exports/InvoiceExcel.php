<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\NumberFormatter;


class InvoiceExcel implements FromCollection, WithHeadings
{
    protected $judulId;

    public function __construct($judulId)
    {
        $this->judulId = $judulId;
    }

    public function collection()
    {
        return Pembayaran::with('siswa', 'kelas')->where('judul_id', $this->judulId)->get()->map(function ($item) {
            // Format the gross amount as rupiah
            $grossAmount = "Rp " . number_format($item->gross_amount, 0, ',', '.');

            return [
                $item->judul->name,
                $item->siswa->name,
                $item->kelas->name,
                $item->category_kelas,
                $grossAmount,
                $item->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            "Nama Pembayaran",
            "Nama Siswa",
            "Kelas",
            "Kategori Kelas",
            "Total Pembayaran",
            "Status Pembayaran",
        ];
    }
}
