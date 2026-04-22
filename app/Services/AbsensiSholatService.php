<?php

namespace App\Services;

use App\Models\AbsensiSholat;
use App\Models\Karyawan;
use App\Models\DeviceAbsensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AbsensiSholatService
{
    protected KmlService     $kmlService;
    protected AbsensiService $absensiService;

    public function __construct(KmlService $kmlService, AbsensiService $absensiService)
    {
        $this->kmlService     = $kmlService;
        $this->absensiService = $absensiService;
    }

    // =========================================================================
    // HELPER: Tentukan Jenis Sholat Berdasarkan Waktu
    // =========================================================================

    private function getJenisSholat(Carbon $waktu): string
    {
        $jam = $waktu->format('H:i:s');

        if ($jam >= '05:30:00' && $jam < '11:30:00') {
            return 'duha';
        } elseif ($jam >= '11:30:00') {
            return 'dzuhur';
        }

        return 'duha';
    }

    private function isWaktuOperasional(Carbon $waktu): bool
    {
        $jam = $waktu->format('H:i:s');
        if ($waktu->isWeekend()) {
            return false;
        }
        return ($jam >= '05:30:00');
    }

    private function getPesanBerhasil(string $jenisSholat, int $totalHariIni): string
    {
        $namaSholat = $jenisSholat === 'duha' ? 'Sholat Duha' : 'Sholat Dzuhur';
        return "{$namaSholat} berhasil dicatat. Total {$totalHariIni}x hari ini.";
    }

    // =========================================================================
    // ABSEN SHOLAT
    // =========================================================================

    public function absenSholat(
        $userId,
        $latitude,
        $longitude,
        $ipAddress = null,
        $userAgent = null,
        $deviceId  = null
    ): array {
        $karyawan = Karyawan::where('user_id', $userId)->first();

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $now            = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();
        $jenisSholat   = $this->getJenisSholat($now);

        if (!$this->isWaktuOperasional($now)) {
            $hari = $now->isWeekend() ? 'Hari ini adalah hari libur' : 'Absensi hanya dapat dilakukan pada jam 05:30 - 14:00 WITA';
            return [
                'success' => false,
                'message' => $hari
            ];
        }

        $sudahAbsen = AbsensiSholat::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->where('jenis_sholat', $jenisSholat)
            ->lockForUpdate()
            ->first();

        if ($sudahAbsen) {
            $namaSholat = $jenisSholat === 'duha' ? 'Sholat Duha' : 'Sholat Dzuhur';
            return [
                'success' => false,
                'message' => "Anda sudah absen {$namaSholat} hari ini."
            ];
        }

        if ($ipAddress && $userAgent) {
            $deviceValidation = $this->absensiService->validasiDevice(
                $karyawan->id, $ipAddress, $userAgent, $deviceId
            );

            if (!$deviceValidation['valid']) {
                return ['success' => false, 'message' => $deviceValidation['message']];
            }
        } else {
            $deviceValidation = null;
        }

        if (config('absensi.use_kml', false)) {
            $lokasiResult = $this->kmlService->validateLocationByType($latitude, $longitude, 'sholat');

            if (!$lokasiResult['valid']) {
                return [
                    'success' => false,
                    'message' => $lokasiResult['message'] ?? 'Anda tidak berada di area mosques'
                ];
            }

            $areaName = $lokasiResult['area_name'] ?? 'Area Sholat';
        } else {
            $areaName = 'Area Sholat';
        }

        try {
            $record = AbsensiSholat::create([
                'karyawan_id'  => $karyawan->id,
                'tanggal'      => $tanggalHariIni,
                'jam_sholat'   => $now->toTimeString(),
                'jenis_sholat' => $jenisSholat,
                'latitude'     => $latitude,
                'longitude'    => $longitude,
                'area_name'    => $areaName,
                'ip_address'   => $ipAddress,
                'device_id'    => $deviceId,
                'user_agent'   => $userAgent,
            ]);

            $deviceName = $deviceValidation
                ? ($deviceValidation['device']->device_name ?? 'Unknown')
                : 'Unknown';

            Log::info('Absensi Sholat', [
                'user_id'     => $userId,
                'nama'        => $karyawan->name,
                'jenis_sholat' => $jenisSholat,
                'waktu'       => $now->toDateTimeString(),
                'area'        => $areaName,
                'ip'          => $ipAddress,
                'device'      => $deviceName,
            ]);

            $totalHariIni = AbsensiSholat::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $tanggalHariIni)
                ->count();

            return [
                'success' => true,
                'message' => $this->getPesanBerhasil($jenisSholat, $totalHariIni),
                'data'    => [
                    'nama'           => $karyawan->name,
                    'jenis_sholat'   => $jenisSholat,
                    'nama_sholat'    => $jenisSholat === 'duha' ? 'Sholat Duha' : 'Sholat Dzuhur',
                    'jam_sholat'     => $now->format('H:i:s'),
                    'area'           => $areaName,
                    'device'         => $deviceName,
                    'total_hari_ini' => $totalHariIni,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error simpan absensi mosques', [
                'user_id' => $userId,
                'error'   => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data absensi mosques.'];
        }
    }

    // =========================================================================
    // RIWAYAT
    // =========================================================================

    public function getRiwayat($userId, $bulan = null, $tahun = null): array
    {
        $karyawan = Karyawan::where('user_id', $userId)->first();

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $query = AbsensiSholat::where('karyawan_id', $karyawan->id);

        if ($bulan && $tahun) {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } else {
            $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);
        }

        $records = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_sholat', 'desc')
            ->get()
            ->map(fn ($item) => [
                'tanggal'      => $item->tanggal->toDateString(),
                'hari'         => $item->tanggal->locale('id')->dayName,
                'jam_sholat'   => Carbon::parse($item->jam_sholat)->format('H:i'),
                'jenis_sholat' => $item->jenis_sholat,
                'nama_sholat'  => $item->jenis_sholat === 'duha' ? 'Sholat Duha' : 'Sholat Dzuhur',
                'area'         => $item->area_name ?? '-',
            ]);

        $grouped = $records->groupBy('tanggal')->map(function ($items, $tanggal) {
            $duha = $items->where('jenis_sholat', 'duha')->count();
            $dzuhur = $items->where('jenis_sholat', 'dzuhur')->count();
            
            return [
                'tanggal'  => $tanggal,
                'hari'     => $items->first()['hari'],
                'duha'     => $duha,
                'dzuhur'   => $dzuhur,
                'total'    => $duha + $dzuhur,
                'detail'   => $items->values(),
            ];
        })->values();

        $summary = [
            'total_duha'   => $records->where('jenis_sholat', 'duha')->count(),
            'total_dzuhur' => $records->where('jenis_sholat', 'dzuhur')->count(),
        ];

        return [
            'success' => true,
            'data'    => [
                'pegawai' => [
                    'nama'    => $karyawan->name,
                    'jabatan' => $karyawan->user?->roles?->first()?->name ?? '-',
                ],
                'riwayat'    => $grouped,
                'summary'    => $summary,
                'total_bulan' => $records->count(),
            ]
        ];
    }

    public function getStatusHariIni($userId): array
    {
        $karyawan = Karyawan::where('user_id', $userId)->first();

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $now            = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();

        $absensiHariIni = AbsensiSholat::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->get();

        return [
            'success'        => true,
            'tanggal'       => $tanggalHariIni,
            'duha_selesai'  => $absensiHariIni->where('jenis_sholat', 'duha')->isNotEmpty(),
            'dzuhur_selesai' => $absensiHariIni->where('jenis_sholat', 'dzuhur')->isNotEmpty(),
        ];
    }
}
