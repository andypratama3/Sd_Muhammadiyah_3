<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapitulasiSheet implements FromView, WithTitle, WithStyles, WithEvents
{
    protected $namaKelas;
    protected $siswaList;
    protected $bulan;

    public function __construct($namaKelas, $siswaList, $bulan)
    {
        $this->namaKelas = $namaKelas;
        $this->siswaList = $siswaList;
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        return view('dashboard.data.charge.rekapitulasi', [
            'namaKelas' => $this->namaKelas,
            'siswaList' => $this->siswaList,
            'bulan' => $this->bulan,
        ]);
    }

    public function title(): string
    {
        return substr($this->namaKelas, 0, 31); // Maksimal 31 karakter
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1:Z1000' => ['font' => ['size' => 11]], // Font default
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $jumlahKolom = 2 + count($this->bulan) + 2; // No, Nama, Bulan SPP, DPP1, DPP2
                $lastColumn = $sheet->getCellByColumnAndRow($jumlahKolom, 4)->getColumn();

                // === 1. Merge judul "REKAPITULASI" hingga kolom terakhir (tanpa border) ===
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->mergeCells('A3:' . $lastColumn . '3');
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // === 2. Warna header untuk No, Nama, Bulan, DPP1, DPP2 ===
                $headerRange = 'A4:' . $lastColumn . '5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD'] // Biru tua
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);

                // === 3. Border untuk semua tabel data ===
                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A4:' . $lastColumn . $lastRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);

                // === 4. Rata kiri kolom Nama (kolom B) ===
                $sheet->getStyle('B6:B' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // === 5. Auto-size semua kolom ===
                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}
