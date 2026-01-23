<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\LokasiAbsensi;
use App\Models\JamKerja;
use App\Models\PengajuanCuti;
use Carbon\Carbon;

class AbsensiService
{
    public function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function validasiLokasi($latitude, $longitude, LokasiAbsensi $lokasi)
    {
        $jarak = $this->hitungJarak(
            $latitude,
            $longitude,
            $lokasi->latitude,
            $lokasi->longitude
        );

        return [
            'valid' => $jarak <= $lokasi->radius,
            'jarak' => round($jarak, 2)
        ];
    }

    public function getJamKerja($jenisPegawai)
    {
        $hari = strtolower(Carbon::now('Asia/Makassar')->locale('id')->dayName);

        $jamKerja = JamKerja::where('jenis_pegawai', $jenisPegawai)
            ->where(function($query) use ($hari) {
                $query->where('hari', $hari)
                      ->orWhere('is_default', true);
            })
            ->orderBy('is_default', 'asc')
            ->first();


        if (!$jamKerja) {
            throw new \Exception('Jam kerja untuk ' . $jenisPegawai . ' belum dikonfigurasi');
        }

        return $jamKerja;
    }

    public function cekStatusCuti($karyawanId, $tanggal)
    {
        $pengajuan = PengajuanCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();

        return $pengajuan;
    }

    public function getJenisPegawaiFromRole(Karyawan $karyawan)
    {

        $role = $karyawan->user?->roles?->first();

        if($role->name == 'guru') {
            return 'guru';
        } else if($role->name == 'tenaga-pendidikan') {
            return 'tenaga-pendidikan';
        }

        return '';
    }

    public function absenMasuk($nip, $latitude, $longitude, $lokasiId = 1)
    {
        $karyawan = Karyawan::whereHas('user', function($query) use ($nip) {
            $query->where('nip', $nip);
        })->first();

        if (!$karyawan) {
            return [
                'success' => false,
                'message' => 'NIP tidak ditemukan atau karyawan tidak aktif'
            ];
        }

        $now = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();


        $cutiAktif = $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        if ($cutiAktif) {
            return [
                'success' => false,
                'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."
            ];
        }

        $lokasi = LokasiAbsensi::where('id', $lokasiId)
            ->where('status', 'aktif')
            ->first();

        if (!$lokasi) {
            return [
                'success' => false,
                'message' => 'Lokasi absensi tidak ditemukan'
            ];
        }

        $validasiLokasi = $this->validasiLokasi($latitude, $longitude, $lokasi);

        if (!$validasiLokasi['valid']) {
            return [
                'success' => false,
                'message' => "Anda berada {$validasiLokasi['jarak']} meter dari lokasi. Radius maksimal {$lokasi->radius} meter.",
            ];
        }

        try {
            $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);
            $jamKerja = $this->getJamKerja($jenisPegawai);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        $batasMasuk = Carbon::parse($jamKerja->batas_masuk)->setTimezone('Asia/Makassar')->format('H:i:s');

        if ($now->greaterThan($batasMasuk)) {
            return [
                'success' => false,
                'message' => 'Anda terlambat melewati batas jam masuk, Silahkan Pulang'
            ];
        }


        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->first();

        if ($absensiHariIni && $absensiHariIni->jam_masuk) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini pada jam ' .
                    Carbon::parse($absensiHariIni->jam_masuk)->format('H:i')
            ];
        }


        $absensi = Absensi::updateOrCreate(
            [
                'karyawan_id' => $karyawan->id,
                'tanggal' => $tanggalHariIni
            ],
            [
                'lokasi_absensi_id' => $lokasi->id,
                'jam_kerja_id' => $jamKerja->id,
                'status_kehadiran' => 'hadir',
                'jam_masuk' => $now->toTimeString(),
                'latitude_masuk' => $latitude,
                'longitude_masuk' => $longitude,
                'jarak_masuk' => $validasiLokasi['jarak'],
                'status_masuk' => 'tepat_waktu'
            ]
        );

        return [
            'success' => true,
            'message' => 'Absensi masuk berhasil',
            'data' => [
                'nama' => $karyawan->name,
                'nip' => $nip,
                'jam_masuk' => $now->format('H:i:s'),
                'jam_kerja' => Carbon::parse($jamKerja->jam_masuk)->format('H:i'),
                'lokasi' => $lokasi->nama_lokasi
            ]
        ];
    }


    public function absenPulang($nip, $latitude, $longitude, $lokasiId = 1)
    {
        $karyawan = Karyawan::whereHas('user', function($query) use ($nip) {
            $query->where('nip', $nip);
        })->first();

        if (!$karyawan) {
            return [
                'success' => false,
                'message' => 'NIP tidak ditemukan atau karyawan tidak aktif'
            ];
        }

        $now = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();


        $cutiAktif = $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        if ($cutiAktif) {
            return [
                'success' => false,
                'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."
            ];
        }

        $lokasi = LokasiAbsensi::find($lokasiId);
        if (!$lokasi) {
            return [
                'success' => false,
                'message' => 'Lokasi absensi tidak ditemukan'
            ];
        }

        $validasiLokasi = $this->validasiLokasi($latitude, $longitude, $lokasi);

        if (!$validasiLokasi['valid']) {
            return [
                'success' => false,
                'message' => "Anda berada {$validasiLokasi['jarak']} meter dari lokasi.",
                'jarak' => $validasiLokasi['jarak']
            ];
        }

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return [
                'success' => false,
                'message' => 'Anda belum melakukan absensi masuk hari ini'
            ];
        }

        if ($absensi->jam_pulang) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pulang pada jam ' . Carbon::parse($absensi->jam_pulang)->format('H:i')
            ];
        }

        $batasPulang = Carbon::parse($absensi->jamKerja->batas_pulang);
        $statusPulang = $now->greaterThanOrEqualTo($batasPulang) ? 'tepat_waktu' : 'pulang_cepat';

        $absensi->update([
            'jam_pulang' => $now->toTimeString(),
            'latitude_pulang' => $latitude,
            'longitude_pulang' => $longitude,
            'jarak_pulang' => $validasiLokasi['jarak'],
            'status_pulang' => $statusPulang
        ]);

        return [
            'success' => true,
            'message' => "Absensi pulang berhasil!",
            'data' => [
                'nama' => $karyawan->name,
                'nip' => $nip,
                'jam_masuk' => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                'jam_pulang' => $now->format('H:i:s'),
                'status' => $statusPulang,
                'jarak' => $validasiLokasi['jarak'] . ' meter'
            ]
        ];
    }

    public function getRiwayatAbsensi($nip, $bulan = null, $tahun = null)
    {
        $karyawan = Karyawan::whereHas('user', function($query) use ($nip) {
            $query->where('nip', $nip);
        })->first();

        if (!$karyawan) {
            return [
                'success' => false,
                'message' => 'NIP tidak ditemukan'
            ];
        }

        $query = Absensi::where('karyawan_id', $karyawan->id)
            ->with(['lokasiAbsensi', 'jamKerja']);

        if ($bulan && $tahun) {
            $query->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get()->map(function($item) {
            return [
                'tanggal' => $item->tanggal,
                'jam_masuk' => $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : null,
                'jam_pulang' => $item->jam_pulang ? Carbon::parse($item->jam_pulang)->format('H:i') : null,
                'status_masuk' => $item->status_masuk,
                'status_pulang' => $item->status_pulang,
                'jarak_masuk' => $item->jarak_masuk ? round($item->jarak_masuk) : null,
                'jarak_pulang' => $item->jarak_pulang ? round($item->jarak_pulang) : null,
                'lokasi' => $item->lokasiAbsensi?->nama_lokasi ?? '-',
            ];
        });


        $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);

        return [
            'success' => true,
            'data' => [
                'pegawai' => [
                    'nama' => $karyawan->name,
                    'nip' => $nip,
                    'jabatan' => $karyawan->user?->roles?->first()?->name ?? '-',
                    'unit_kerja' => $karyawan->user?->roles?->first()?->name ?? '-',
                ],
                'riwayat' => $riwayat
            ]
        ];
    }
}
