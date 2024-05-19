<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\NumberFormatter;


class InvoiceExcel implements FromCollection, WithHeadings
{
    protected $judulId;

    public function __construct($judulId,$kelas,$category_kelas)
    {
        $this->judulId = $judulId;
        $this->kelas = $kelas;
        $this->category_kelas = $category_kelas;
    }

    public function collection()
    {
        $query = Pembayaran::with(['siswa', 'kelas'])
            ->where('judul_id', $this->judulId)
            ->whereHas('siswa', function ($query) {
                $query->whereNotIn('name', ['lulus']);
            });

        if ($this->kelas) {
            $query->where('kelas_id', $this->kelas);
        }

        if ($this->category_kelas) {
            $query->where('category_kelas', $this->category_kelas);
        }

        $query->orderBy('created_at', 'desc');

        return $query->get()->map(function ($item) {
            $grossAmount = number_format($item->gross_amount, 0, ',', '.');

            return [
                $item->judul->name,
                $item->siswa->name,
                $item->kelas->name,
                $item->category_kelas,
                "Rp {$grossAmount}",
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
