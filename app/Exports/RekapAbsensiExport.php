<?php

namespace App\Exports;

use App\Models\Karyawan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get date filter from request
     */
    private function getDateFilter()
    {
        $start = null;
        $end = null;

        try {
            $dateStr = $this->request->get('date', '');

            if (!empty($dateStr) && is_string($dateStr)) {
                if (strpos($dateStr, ' : ') !== false) {
                    $parts = explode(' : ', $dateStr);

                    if (count($parts) >= 2) {
                        $startStr = trim($parts[0]);
                        $endStr = trim($parts[1]);

                        if (!empty($startStr) && !empty($endStr)) {
                            $start = Carbon::createFromFormat('d-m-Y', $startStr)->startOfDay();
                            $end = Carbon::createFromFormat('d-m-Y', $endStr)->endOfDay();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('RekapAbsensiExport getDateFilter exception', [
                'error' => $e->getMessage()
            ]);
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Start cell
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * Ambil data dari database dengan filter
     */
    public function collection()
    {
        try {
            $dateFilter = $this->getDateFilter();
            $start = $dateFilter['start'];
            $end = $dateFilter['end'];

            \Log::info('RekapAbsensiExport - Date Filter', [
                'start' => $start ? $start->format('Y-m-d H:i:s') : 'null',
                'end' => $end ? $end->format('Y-m-d H:i:s') : 'null'
            ]);

            // Get karyawans dengan absensi data sesuai filter
            $karyawans = Karyawan::with(['absensi' => function ($q) use ($start, $end) {
                if ($start && $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                }
                $q->orderBy('tanggal', 'asc');
            }]);

            // Load counts
            $karyawans->withCount([
                'absensi as hadir_count' => function ($q) use ($start, $end) {
                    $q->where('status_kehadiran', 'hadir');
                    if ($start && $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                },
                'absensi as cuti_count' => function ($q) use ($start, $end) {
                    $q->where('status_kehadiran', 'cuti');
                    if ($start && $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                },
                'absensi as izin_count' => function ($q) use ($start, $end) {
                    $q->where('status_kehadiran', 'izin');
                    if ($start && $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                },
                'absensi as sakit_count' => function ($q) use ($start, $end) {
                    $q->where('status_kehadiran', 'sakit');
                    if ($start && $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                },
                'absensi as alpha_count' => function ($q) use ($start, $end) {
                    $q->where('status_kehadiran', 'alpha');
                    if ($start && $end) {
                        $q->whereBetween('tanggal', [$start, $end]);
                    }
                }
            ]);

            // Filter berdasarkan role
            if (!Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
                $karyawans->where('id', Auth::user()->karyawan->id);
            }

            $karyawans = $karyawans->get();

            \Log::info('RekapAbsensiExport - Data Retrieved', [
                'karyawan_count' => $karyawans->count()
            ]);

            $data = [];

            // ===== HEADER SECTION =====
            $data[] = ['REKAP ABSENSI KARYAWAN'];
            $data[] = ['PT / Sekolah / Instansi XYZ'];
            $data[] = [''];
            $data[] = ['Periode: ' . ($this->request->filled('date') ? $this->request->date : 'Semua Tanggal')];
            $data[] = ['Dicetak pada: ' . now()->format('d-m-Y H:i:s')];
            $data[] = [''];

            // ===== RINGKASAN ABSENSI =====
            $data[] = ['RINGKASAN ABSENSI'];
            $data[] = [
                'No',
                'Nama Karyawan',
                'NIP',
                'Hadir',
                'Cuti',
                'Izin',
                'Sakit',
                'Alpha',
                'Total'
            ];

            $no = 1;
            foreach ($karyawans as $karyawan) {
                $total = ($karyawan->hadir_count ?? 0) + ($karyawan->cuti_count ?? 0) +
                         ($karyawan->izin_count ?? 0) + ($karyawan->sakit_count ?? 0) +
                         ($karyawan->alpha_count ?? 0);

                $data[] = [
                    $no++,
                    $karyawan->name ?? '-',
                    $karyawan->nip ?? '-',
                    $karyawan->hadir_count ?? 0,
                    $karyawan->cuti_count ?? 0,
                    $karyawan->izin_count ?? 0,
                    $karyawan->sakit_count ?? 0,
                    $karyawan->alpha_count ?? 0,
                    $total
                ];
            }

            // ===== DETAIL ABSENSI =====
            $data[] = [''];
            $data[] = [''];
            $data[] = ['DETAIL ABSENSI'];
            $data[] = [
                'No',
                'Nama Karyawan',
                'NIP',
                'Tanggal',
                'Hari',
                'Status',
                'Jam Masuk',
                'Jam Pulang',
                'Keterangan'
            ];

            $no = 1;
            $detailCount = 0;
            foreach ($karyawans as $karyawan) {
                if ($karyawan->absensi->isEmpty()) {
                    $data[] = [
                        $no++,
                        $karyawan->name ?? '-',
                        $karyawan->nip ?? '-',
                        '-',
                        '-',
                        'Tidak ada data',
                        '-',
                        '-',
                        '-'
                    ];
                    $detailCount++;
                } else {
                    foreach ($karyawan->absensi as $absen) {
                        try {
                            $data[] = [
                                $no++,
                                $karyawan->name ?? '-',
                                $karyawan->nip ?? '-',
                                Carbon::parse($absen->tanggal)->format('d-m-Y'),
                                Carbon::parse($absen->tanggal)->locale('id')->translatedFormat('l'),
                                $this->formatStatus($absen->status_kehadiran),
                                $absen->jam_masuk ?? '-',
                                $absen->jam_pulang ?? '-',
                                $absen->keterangan ?? '-'
                            ];
                            $detailCount++;
                        } catch (\Exception $e) {
                            \Log::error('Error parsing absensi row', [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            \Log::info('RekapAbsensiExport - Data Prepared', [
                'total_rows' => count($data),
                'detail_count' => $detailCount
            ]);

            return collect($data);
        } catch (\Exception $e) {
            \Log::error('RekapAbsensiExport Collection Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Set header
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Set styling
     */
    public function styles(Worksheet $sheet)
    {
        try {
            $maxRow = $sheet->getHighestRow();

            // ===== TITLE STYLING (Row 1) =====
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Calibri'
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ]);
            $sheet->getRowDimensions()[1]->setRowHeight(25);

            // ===== SUBTITLE STYLING (Row 2) =====
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => '666666']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F4FF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            $sheet->getRowDimensions()[2]->setRowHeight(20);

            // ===== PERIODE INFO (Row 4-5) =====
            $sheet->getStyle('A4:A5')->applyFromArray([
                'font' => [
                    'size' => 10,
                    'bold' => true,
                    'color' => ['rgb' => '1E3A8A']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // ===== SECTION HEADER STYLING (Row 7 & Detail Row) =====
            for ($row = 1; $row <= $maxRow; $row++) {
                try {
                    $cellValue = (string)($sheet->getCell("A{$row}")->getValue() ?? '');

                    if ($cellValue === 'RINGKASAN ABSENSI') {
                        $sheet->mergeCells("A{$row}:I{$row}");
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '1E3A8A']
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                        $sheet->getRowDimensions()[$row]->setRowHeight(22);
                    }

                    if ($cellValue === 'DETAIL ABSENSI') {
                        $sheet->mergeCells("A{$row}:I{$row}");
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '1E3A8A']
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                        $sheet->getRowDimensions()[$row]->setRowHeight(22);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // ===== HEADER TABLE STYLING =====
            for ($row = 8; $row <= $maxRow; $row++) {
                try {
                    $cellValue = (string)($sheet->getCell("A{$row}")->getValue() ?? '');

                    // Header ringkasan (row setelah "RINGKASAN ABSENSI")
                    if ($row === 8 || (isset($prevValue) && $prevValue === 'RINGKASAN ABSENSI' && $row === $row)) {
                        $this->styleHeaderRow($sheet, $row);
                    }

                    // Header detail
                    if ($cellValue === 'No' && $sheet->getCell("B{$row}")->getValue() === 'Nama Karyawan') {
                        $this->styleHeaderRow($sheet, $row);
                    }

                    $prevValue = $cellValue;
                } catch (\Exception $e) {
                    continue;
                }
            }

            // ===== APPLY HEADER ROW 8 =====
            $this->styleHeaderRow($sheet, 8);

            // ===== FIND DETAIL HEADER AND STYLE =====
            for ($row = 1; $row <= $maxRow; $row++) {
                try {
                    $cellA = (string)($sheet->getCell("A{$row}")->getValue() ?? '');
                    $cellB = (string)($sheet->getCell("B{$row}")->getValue() ?? '');

                    if ($cellA === 'No' && $cellB === 'Nama Karyawan' && $row > 10) {
                        $this->styleHeaderRow($sheet, $row);
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // ===== APPLY BORDERS TO ALL DATA ROWS =====
            // Border untuk ringkasan data (row 8 sampai sebelum "DETAIL ABSENSI")
            $detailStartRow = null;
            for ($row = 8; $row <= $maxRow; $row++) {
                $cellValue = (string)($sheet->getCell("A{$row}")->getValue() ?? '');
                if ($cellValue === 'DETAIL ABSENSI') {
                    $detailStartRow = $row;
                    break;
                }
            }

            // Border untuk ringkasan section
            if ($detailStartRow !== null) {
                $sheet->getStyle("A8:I" . ($detailStartRow - 2))->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                // Alternating colors untuk ringkasan
                for ($row = 9; $row < $detailStartRow; $row++) {
                    if (($row - 9) % 2 == 0) {
                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F5F5F5']
                            ]
                        ]);
                    }
                }
            }

            // Border untuk detail section
            if ($detailStartRow !== null && $detailStartRow < $maxRow) {
                $sheet->getStyle("A" . ($detailStartRow + 2) . ":I{$maxRow}")->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                // Alternating colors untuk detail
                for ($row = $detailStartRow + 3; $row <= $maxRow; $row++) {
                    if (($row - ($detailStartRow + 3)) % 2 == 0) {
                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F5F5F5']
                            ]
                        ]);
                    }
                }
            }

            // ===== CENTER ALIGNMENT FOR NUMERIC COLUMNS =====
            for ($row = 8; $row <= $maxRow; $row++) {
                try {
                    foreach (['A', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                        $sheet->getStyle("{$col}{$row}")->applyFromArray([
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // ===== MERGE CELLS & BORDERS UNTUK HEADER =====
            try {
                // Merge row 1 (Title)
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                // Merge row 2 (Subtitle)
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D0D0']
                        ]
                    ]
                ]);

                // Merge row 4 (Periode)
                $sheet->mergeCells('A4:I4');
                $sheet->getStyle('A4')->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D0D0']
                        ]
                    ]
                ]);

                // Merge row 5 (Dicetak pada)
                $sheet->mergeCells('A5:I5');
                $sheet->getStyle('A5')->applyFromArray([
                    'border' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D0D0']
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::warning('Error merging cells: ' . $e->getMessage());
            }

            \Log::info('RekapAbsensiExport styles - Complete');

        } catch (\Exception $e) {
            \Log::error('RekapAbsensiExport styles error', [
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Style header row
     */
    private function styleHeaderRow($sheet, $row)
    {
        try {
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'border' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '1E3A8A']
                    ]
                ]
            ]);
            $sheet->getRowDimensions()[$row]->setRowHeight(25);
        } catch (\Exception $e) {
            \Log::warning("Error styling header row {$row}: " . $e->getMessage());
        }
    }

    /**
     * Set column width
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,      // No
            'B' => 25,     // Nama Karyawan
            'C' => 14,     // NIP
            'D' => 12,     // Hadir / Tanggal
            'E' => 12,     // Cuti / Hari
            'F' => 12,     // Izin / Status
            'G' => 12,     // Sakit / Jam Masuk
            'H' => 12,     // Alpha / Jam Pulang
            'I' => 20,     // Total / Keterangan
        ];
    }

    /**
     * Format status kehadiran
     */
    private function formatStatus($status)
    {
        $statuses = [
            'hadir' => 'Hadir',
            'cuti' => 'Cuti',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
        ];

        return $statuses[$status] ?? ucfirst($status);
    }
}
