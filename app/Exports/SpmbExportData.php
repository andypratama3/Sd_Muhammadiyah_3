<?php

namespace App\Exports;

use App\Models\Spmb;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpmbExportData implements FromCollection, WithHeadings, WithStyles
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        return Spmb::whereYear('created_at', $this->year)
            ->orderBy('nomor_urut', 'asc')
            ->get([
                'nomor_urut',
                'nama',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'agama',
                'suku',
                'alamat',
                'nama_asal_sekolah',
                'sttb',
                'alamat_sekolah',
                'select_data',
                'nama_ayah',
                'nama_ibu',
                'pendidikan_ayah',
                'pendidikan_ibu',
                'pekerjaan_ayah',
                'pekerjaan_ibu',
                'alamat_ayah',
                'alamat_ibu',
                'nama_wali',
                'pekerjaan_wali',
                'alamat_wali',
                'phone',
                'status_pembayaran',
                'status',
                'order_id',
            ]);
    }

    public function headings(): array
    {
        return [
            'Nomor Urut',
            'Nama',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Suku',
            'Alamat',
            'Asal Sekolah',
            'STTB',
            'Alamat Sekolah',
            'Select Data',
            'Nama Ayah',
            'Nama Ibu',
            'Pendidikan Ayah',
            'Pendidikan Ibu',
            'Pekerjaan Ayah',
            'Pekerjaan Ibu',
            'Alamat Ayah',
            'Alamat Ibu',
            'Nama Wali',
            'Pekerjaan Wali',
            'Alamat Wali',
            'Phone',
            'Status Pembayaran',
            'Status SPMB',
            'Order ID',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Mulai row data di baris 2 (karena baris 1 untuk heading)
        $startRow = 2;
        $rowCount = $sheet->getHighestRow();

        for ($row = $startRow; $row <= $rowCount; $row++) {
            // Ambil nilai status pembayaran dan status SPMB di kolom masing-masing
            $statusPembayaran = strtolower($sheet->getCell("Y{$row}")->getValue()); // Kolom 25 (Y)
            $statusSpmb = strtolower($sheet->getCell("Z{$row}")->getValue()); // Kolom 26 (Z)

            // Warnai kolom Status Pembayaran (Y)
            if ($statusPembayaran === 'settlement' || $statusPembayaran === 'paid') {
                // Hijau untuk berhasil
                $sheet->getStyle("Y{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
            } elseif ($statusPembayaran === 'pending') {
                // Kuning untuk pending
                $sheet->getStyle("Y{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFEB9C');
            } else {
                // Merah untuk gagal atau status lain
                $sheet->getStyle("Y{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
            }

            // Warnai kolom Status SPMB (Z)
            if ($statusSpmb === 'diterima') {
                $sheet->getStyle("Z{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE'); // hijau
            } elseif ($statusSpmb === 'pending') {
                $sheet->getStyle("Z{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFEB9C'); // kuning
            } elseif ($statusSpmb === 'tidak_diterima') {
                $sheet->getStyle("Z{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE'); // merah
            }
        }

        // Style heading bold dan tengah
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Z1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
