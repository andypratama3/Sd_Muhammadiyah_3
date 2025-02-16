<?php

namespace App\Exports;

use App\Models\Charge;
use App\Models\Kelas;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ChargePerClassExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $kelasGroup;
    protected $totalByCategory = [];
    protected $data = [];

    public function __construct($startDate, $endDate, $kelasGroup)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->kelasGroup = $kelasGroup;
    }

    public function query()
    {
        $charges = Charge::with('siswa.kelas')
            ->whereHas('siswa.kelas', function ($query) {
                $query->where('name', 'like', "{$this->kelasGroup}%");
            })
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                'order_id',
                'siswa_id',
                'gross_amount as total_bayar',
                'payment_type as jenis_bayar',
                'bank',
                'va_number',
                'transaction_id as id_transaksi',
                'created_at',
                'transaction_status as status'
            )
            ->get();

        // Grupkan data berdasarkan kategori kelas
        foreach ($charges as $charge) {
            $kelasName = $charge->siswa->kelas->name ?? 'Tanpa Kelas';
            $this->data[$kelasName][] = $charge;
            $this->totalByCategory[$kelasName] = ($this->totalByCategory[$kelasName] ?? 0) + $charge->total_bayar;
        }

        return collect($charges);
    }

    public function map($charge): array
    {
        return [
            $charge->order_id,
            $charge->siswa->name ?? 'N/A',
            "Rp " . number_format($charge->total_bayar, 2, ',', '.'),
            $charge->jenis_bayar,
            $charge->bank ?? 'N/A',
            $charge->va_number ?? 'N/A',
            $charge->id_transaksi,
            Carbon::now()->locale('id_ID')->format('d F Y'),
            $charge->status,
        ];
    }

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

    public function title(): string
    {
        return "Kelas " . $this->kelasGroup;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $row = 2;
                foreach ($this->data as $kelasName => $charges) {
                    // Tambahkan header kategori kelas
                    $event->sheet->mergeCells("A{$row}:I{$row}");
                    $event->sheet->setCellValue("A{$row}", $kelasName);
                    $event->sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $row++;

                    // Tambahkan data
                    foreach ($charges as $charge) {
                        $event->sheet->appendRows([$this->map($charge)], $row);
                        $row++;
                    }

                    // Tambahkan subtotal
                    $event->sheet->mergeCells("A{$row}:B{$row}");
                    $event->sheet->setCellValue("A{$row}", "Sub Total:");
                    $event->sheet->setCellValue("C{$row}", "Rp " . number_format($this->totalByCategory[$kelasName], 2, ',', '.'));
                    $event->sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
                    $row += 2; 
                }
            },
        ];
    }
}
