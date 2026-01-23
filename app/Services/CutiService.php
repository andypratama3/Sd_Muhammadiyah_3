<?php

namespace App\Services;

use App\Models\PengajuanCuti;
use App\Models\Pegawai;
use App\Models\Absensi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CutiService
{
    /**
     * Ajukan cuti/izin
     */
    public function ajukanCuti($data)
    {
        $pegawai = Karyawan::where('nip', $data['nip'])->first();

        if (!$pegawai) {
            return [
                'success' => false,
                'message' => 'NIP tidak ditemukan'
            ];
        }

        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);

        if ($tanggalSelesai->lt($tanggalMulai)) {
            return [
                'success' => false,
                'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai'
            ];
        }

        $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        // Cek apakah ada pengajuan yang overlap
        $overlap = PengajuanCuti::where('pegawai_id', $pegawai->id)
            ->where('status', '!=', 'ditolak')
            ->where(function($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                      ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                      ->orWhere(function($q) use ($tanggalMulai, $tanggalSelesai) {
                          $q->where('tanggal_mulai', '<=', $tanggalMulai)
                            ->where('tanggal_selesai', '>=', $tanggalSelesai);
                      });
            })
            ->exists();

        if ($overlap) {
            return [
                'success' => false,
                'message' => 'Sudah ada pengajuan cuti/izin pada periode tersebut'
            ];
        }

        $pengajuan = PengajuanCuti::create([
            'pegawai_id' => $pegawai->id,
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jumlah_hari' => $jumlahHari,
            'alasan' => $data['alasan'],
            'file_pendukung' => $data['file_pendukung'] ?? null,
            'status' => 'menunggu'
        ]);

        return [
            'success' => true,
            'message' => 'Pengajuan berhasil diajukan dan menunggu persetujuan admin',
            'data' => $pengajuan
        ];
    }

    /**
     * Setujui/tolak pengajuan cuti (Admin)
     */
    public function prosesPengajuan($pengajuanId, $status, $adminId, $catatan = null)
    {
        $pengajuan = PengajuanCuti::find($pengajuanId);

        if (!$pengajuan) {
            return [
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan'
            ];
        }

        if ($pengajuan->status !== 'menunggu') {
            return [
                'success' => false,
                'message' => 'Pengajuan sudah diproses sebelumnya'
            ];
        }
        // if ($status === '') {
    }
}
