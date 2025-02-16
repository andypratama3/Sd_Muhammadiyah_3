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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ChargeExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents
{
    protected $tanggalMulai;
    protected $tanggalSelesai;
    protected $kelas;
    protected $totalBayar = 0;
    protected $nomor = 1; // Tambahkan variabel untuk auto increment

    public function __construct($tanggalMulai, $tanggalSelesai, $kelas = null)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->kelas = $kelas;
    }

    public function query()
    {
        $tagihan = Charge::with('siswa.kelas')
            ->whereBetween('created_at', [$this->tanggalMulai, $this->tanggalSelesai]);

        if ($this->kelas) {
            $tagihan->whereHas('siswa.kelas', function ($query) {
                $query->where('id', $this->kelas);
            });
        }

        return $tagihan;
    }

    public function map($tagihan): array
    {
        $data = [
            $this->nomor++, // Tambahkan nomor auto increment
            $tagihan->order_id,
            optional($tagihan->siswa)->name ?? 'Tidak Ada',
            "Rp " . number_format($tagihan->gross_amount, 2, ',', '.'),
            $tagihan->payment_type,
            $tagihan->bank ?? 'Tidak Ada',
            $tagihan->va_number ?? 'Tidak Ada',
            $tagihan->transaction_id,
            Carbon::parse($tagihan->created_at)->format('d-m-Y H:i'),
            $this->translateStatus($tagihan->transaction_status),
        ];

        $this->totalBayar += $tagihan->gross_amount;

        return $data;
    }

    public function headings(): array
    {
        return [
            "No", // Tambahkan kolom nomor
            "ID Tagihan",
            "Nama Siswa",
            "Jumlah Bayar",
            "Jenis Pembayaran",
            "Bank",
            "Nomor Virtual Account",
            "ID Transaksi",
            "Tanggal Transaksi",
            "Status Transaksi",
        ];
    }

    public function title(): string
    {
        $kelas = Kelas::find($this->kelas);
        return $kelas ? $kelas->name : "Semua Kelas";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lembar = $event->sheet->getDelegate();
                $jumlahBaris = count($this->query()->get()) + 2;
                $kolomTerakhir = 'J'; // Kolom terakhir setelah menambahkan "No"
                $rentangSel = "A1:{$kolomTerakhir}{$jumlahBaris}";

                // Tambahkan garis tepi pada seluruh sel
                $lembar->getStyle($rentangSel)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Sesuaikan ukuran kolom otomatis
                foreach (range('A', $kolomTerakhir) as $kolom) {
                    $lembar->getColumnDimension($kolom)->setAutoSize(true);
                }

                // Format warna berdasarkan status transaksi
                for ($baris = 2; $baris <= $jumlahBaris; $baris++) {
                    $selStatus = "J{$baris}";
                    $nilaiStatus = $lembar->getCell($selStatus)->getValue();

                    $warnaStatus = [
                        'Menunggu' => 'FFFF00',
                        'Pembayaran Online' => '87CEEB',
                        'Gagal' => 'FF0000',
                        'Bayar Offline' => '5ce70b',
                    ];

                    if (isset($warnaStatus[$nilaiStatus])) {
                        $lembar->getStyle($selStatus)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => $warnaStatus[$nilaiStatus]],
                            ],
                            'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                        ]);
                    }
                }

                // Tambahkan total di bawah tabel
                $selSubtotal = "D{$jumlahBaris}";
                $selTotal = "E{$jumlahBaris}";

                $lembar->setCellValue($selSubtotal, "Total:");
                $lembar->setCellValue($selTotal, "Rp " . number_format($this->totalBayar, 2, ',', '.'));

                // Jadikan total tebal
                $lembar->getStyle("{$selSubtotal}:{$selTotal}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }

    private function translateStatus($status)
    {
        $statusTerjemahan = [
            'pending' => 'Menunggu',
            'settlement' => 'Pembayaran Online',
            'Expired' => 'Gagal',
            'failed' => 'Gagal',
            'pay_offline' => 'Bayar Offline',
        ];

        return $statusTerjemahan[$status] ?? $status;
    }
}
