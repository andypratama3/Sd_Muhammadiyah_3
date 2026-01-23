<?php

namespace App\Exports;

use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapAbsensiExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Absensi::with('karyawan');

        if (!Auth::user()->hasAnyRole(['admin','superadmin'])) {
            $query->where('karyawan_id', Auth::user()->karyawan->id);
        }

        return $query->get()->map(function ($item) {
            return [
                'Nama' => $item->karyawan->nama ?? '-',
                'Tanggal' => $item->tanggal->format('d-m-Y'),
                'Status' => $item->status_kehadiran,
                'Jam Masuk' => $item->jam_masuk,
                'Jam Pulang' => $item->jam_pulang,
                'Keterangan' => $item->keterangan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Tanggal',
            'Status Kehadiran',
            'Jam Masuk',
            'Jam Pulang',
            'Keterangan',
        ];
    }
}
