<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\LokasiAbsensi;
use App\Models\JamKerja;
use App\Models\PengajuanCuti;
use App\Models\DeviceAbsensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    /**
     * Hitung jarak menggunakan Haversine Formula
     */
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

    /**
     * Validasi lokasi
     */
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

    /**
     * Generate device fingerprint
     */
    public function generateDeviceFingerprint($userAgent, $deviceId = null)
    {
        $data = $userAgent . ($deviceId ?? '');
        return hash('sha256', $data);
    }

    /**
     * Parse device name dari user agent
     */
    private function parseDeviceName($userAgent)
    {
        if (preg_match('/Android/', $userAgent)) {
            return 'Android Phone';
        } elseif (preg_match('/iPhone/', $userAgent)) {
            return 'iPhone';
        } elseif (preg_match('/iPad/', $userAgent)) {
            return 'iPad';
        } elseif (preg_match('/Windows/', $userAgent)) {
            return 'Windows PC';
        } elseif (preg_match('/Macintosh/', $userAgent)) {
            return 'Mac';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux PC';
        }
        return 'Unknown Device';
    }

    /**
     * Validasi device dan IP address
     */
    public function validasiDevice($karyawanId, $ipAddress, $userAgent, $deviceId = null)
    {
        $fingerprint = $this->generateDeviceFingerprint($userAgent, $deviceId);

        // Cari device yang sudah terdaftar
        $device = DeviceAbsensi::where('karyawan_id', $karyawanId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        // Device belum terdaftar
        if (!$device) {
            // Cek jumlah device yang sudah terdaftar
            $existingDevices = DeviceAbsensi::where('karyawan_id', $karyawanId)
                ->where('is_active', true)
                ->count();

            // Batasi maksimal 3 device
            if ($existingDevices >= 3) {
                return [
                    'valid' => false,
                    'message' => 'Anda sudah mendaftarkan maksimal 3 device. Silahkan hubungi admin untuk menghapus device lama.',
                    'device' => null
                ];
            }

            // Auto-register device baru
            $device = DeviceAbsensi::create([
                'karyawan_id' => $karyawanId,
                'device_fingerprint' => $fingerprint,
                'device_name' => $this->parseDeviceName($userAgent),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_id' => $deviceId,
                'is_active' => true,
                'last_used_at' => now(),
                'registered_at' => now()
            ]);

            Log::info('Device baru terdaftar', [
                'karyawan_id' => $karyawanId,
                'device_name' => $device->device_name,
                'ip' => $ipAddress
            ]);

            return [
                'valid' => true,
                'message' => 'Device baru berhasil didaftarkan',
                'device' => $device,
                'is_new_device' => true
            ];
        }

        // Device sudah terdaftar - validasi status
        if (!$device->is_active) {
            return [
                'valid' => false,
                'message' => 'Device Anda telah dinonaktifkan. Silahkan hubungi admin.',
                'device' => $device
            ];
        }

        // Validasi IP address (warning jika berbeda, tapi tetap allow)
        $ipChanged = false;
        if ($device->ip_address !== $ipAddress) {
            $ipChanged = true;

            $device->update([
                'ip_address' => $ipAddress,
                'last_used_at' => now()
            ]);

            Log::warning('IP Address berubah', [
                'karyawan_id' => $karyawanId,
                'old_ip' => $device->ip_address,
                'new_ip' => $ipAddress
            ]);
        } else {
            $device->touch('last_used_at');
        }

        return [
            'valid' => true,
            'message' => $ipChanged ? 'Perhatian: IP Address Anda berbeda dari terakhir kali' : 'Device terverifikasi',
            'device' => $device,
            'is_new_device' => false,
            'ip_changed' => $ipChanged
        ];
    }

    /**
     * Deteksi abuse/manipulasi
     */
    public function deteksiAbuse($karyawanId, $ipAddress)
    {
        $now = Carbon::now('Asia/Makassar');

        // 1. Cek absensi ganda dalam 5 menit
        $recentAbsensi = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', $now->toDateString())
            ->where('created_at', '>', $now->copy()->subMinutes(5))
            ->count();

        if ($recentAbsensi > 0) {
            return [
                'suspicious' => true,
                'reason' => 'Terdeteksi percobaan absensi berulang dalam waktu singkat'
            ];
        }

        // 2. Cek IP sharing (1 IP dipakai banyak user)
        $sameIpCount = Absensi::where('ip_address', $ipAddress)
            ->where('tanggal', $now->toDateString())
            ->distinct('karyawan_id')
            ->count('karyawan_id');

        if ($sameIpCount > 5) {
            Log::warning('IP Sharing Detected', [
                'ip' => $ipAddress,
                'total_users' => $sameIpCount,
                'date' => $now->toDateString()
            ]);

            return [
                'suspicious' => true,
                'reason' => 'IP Address ini digunakan oleh banyak karyawan. Harap gunakan device pribadi.'
            ];
        }

        return [
            'suspicious' => false,
            'reason' => null
        ];
    }

    /**
     * Get jam kerja
     */
    public function getJamKerja($jenisPegawai)
    {
        $now = Carbon::now('Asia/Makassar');
        $hari = strtolower($now->locale('id')->dayName);

        $jamKerja = JamKerja::where('jenis_pegawai', $jenisPegawai)
            ->where('hari', $hari)
            ->first();

        if (!$jamKerja) {
            $jamKerja = JamKerja::where('jenis_pegawai', $jenisPegawai)
                ->where('is_default', true)
                ->first();
        }

        if (!$jamKerja) {
            throw new \Exception('Jam kerja untuk ' . $jenisPegawai . ' belum dikonfigurasi');
        }

        return $jamKerja;
    }

    /**
     * Cek status cuti
     */
    public function cekStatusCuti($karyawanId, $tanggal)
    {
        return PengajuanCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();
    }

    /**
     * Get jenis pegawai dari role
     */
    public function getJenisPegawaiFromRole(Karyawan $karyawan)
    {
        $role = $karyawan->user?->roles?->first();

        if (!$role) {
            throw new \Exception('Role user belum ditentukan');
        }

        if ($role->name === 'guru') {
            return 'guru';
        }

        if ($role->name === 'tenaga-pendidikan') {
            return 'tenaga-pendidikan';
        }

        throw new \Exception('Jenis pegawai tidak dikenali: ' . $role->name);
    }

    /**
     * ABSEN MASUK
     */
    public function absenMasuk($nip, $latitude, $longitude, $lokasiId = 1, $ipAddress = null, $userAgent = null, $deviceId = null)
    {
        // 1. VALIDASI KARYAWAN
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

        // 2. VALIDASI DEVICE & IP
        if ($ipAddress && $userAgent) {
            $deviceValidation = $this->validasiDevice(
                $karyawan->id,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            if (!$deviceValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $deviceValidation['message']
                ];
            }

            // Deteksi abuse
            $abuseCheck = $this->deteksiAbuse($karyawan->id, $ipAddress);
            if ($abuseCheck['suspicious']) {
                Log::warning('Suspicious Activity', [
                    'karyawan_id' => $karyawan->id,
                    'nip' => $nip,
                    'ip' => $ipAddress,
                    'reason' => $abuseCheck['reason']
                ]);

                return [
                    'success' => false,
                    'message' => 'Aktivitas mencurigakan: ' . $abuseCheck['reason']
                ];
            }
        }

        // 3. CEK CUTI
        $cutiAktif = $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        if ($cutiAktif) {
            return [
                'success' => false,
                'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."
            ];
        }

        // 4. VALIDASI LOKASI
        $lokasi = LokasiAbsensi::where('id', $lokasiId)
            ->where('status', 'aktif')
            ->first();

        if (!$lokasi) {
            return [
                'success' => false,
                'message' => 'Lokasi absensi tidak ditemukan atau tidak aktif'
            ];
        }

        $validasiLokasi = $this->validasiLokasi($latitude, $longitude, $lokasi);

        if (!$validasiLokasi['valid']) {
            return [
                'success' => false,
                'message' => "Anda berada {$validasiLokasi['jarak']} meter dari lokasi. Radius maksimal {$lokasi->radius} meter.",
                'jarak' => $validasiLokasi['jarak']
            ];
        }

        // 5. AMBIL JAM KERJA
        try {
            $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);
            $jamKerja = $this->getJamKerja($jenisPegawai);

            if (is_null($jamKerja->jam_masuk)) {
                return [
                    'success' => false,
                    'message' => 'Hari ini adalah hari libur. Absensi tidak diperlukan.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // 6. VALIDASI WAKTU
        $batasMasuk = Carbon::parse($jamKerja->batas_masuk, 'Asia/Makassar');
        $jamMasukNormal = Carbon::parse($jamKerja->jam_masuk, 'Asia/Makassar');

        if ($now->greaterThan($batasMasuk)) {
            return [
                'success' => false,
                'message' => 'Anda terlambat melewati batas jam masuk (' .
                            $batasMasuk->format('H:i') . ' WITA). Silahkan Untuk Pulang.'
            ];
        }

        // 7. CEK DUPLIKASI
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->first();

        if ($absensiHariIni && $absensiHariIni->jam_masuk) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini pada jam ' .
                    Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') . ' WITA'
            ];
        }

        // 8. TENTUKAN STATUS
        $statusMasuk = $now->lessThanOrEqualTo($jamMasukNormal)
            ? 'tepat_waktu'
            : 'terlambat';

        // 9. SIMPAN ABSENSI
        try {
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
                    'status_masuk' => $statusMasuk,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'device_id' => $deviceId
                ]
            );

            Log::info('Absensi Masuk', [
                'nip' => $nip,
                'nama' => $karyawan->name,
                'waktu' => $now->toDateTimeString(),
                'status' => $statusMasuk,
                'jarak' => $validasiLokasi['jarak'],
                'ip' => $ipAddress,
                'device' => $deviceValidation['device']->device_name ?? 'Unknown'
            ]);

            return [
                'success' => true,
                'message' => $statusMasuk === 'tepat_waktu'
                    ? 'Absensi masuk berhasil! Anda tepat waktu.'
                    : 'Absensi masuk berhasil! Namun Anda terlambat.',
                'data' => [
                    'nama' => $karyawan->name,
                    'nip' => $nip,
                    'jam_masuk' => $now->format('H:i:s'),
                    'jam_kerja' => $jamMasukNormal->format('H:i'),
                    'lokasi' => $lokasi->nama_lokasi,
                    'status' => $statusMasuk,
                    'jarak' => $validasiLokasi['jarak'] . ' meter',
                    'device' => $deviceValidation['device']->device_name ?? null,
                    'is_new_device' => $deviceValidation['is_new_device'] ?? false
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error simpan absensi masuk', [
                'nip' => $nip,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data absensi.'
            ];
        }
    }

    /**
     * ABSEN PULANG
     */
    public function absenPulang($nip, $latitude, $longitude, $lokasiId = 1, $ipAddress = null, $userAgent = null, $deviceId = null)
    {
        // 1. VALIDASI KARYAWAN
        $karyawan = Karyawan::whereHas('user', function($query) use ($nip) {
            $query->where('nip', $nip);
        })->first();

        if (!$karyawan) {
            return [
                'success' => false,
                'message' => 'NIP tidak ditemukan'
            ];
        }

        $now = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();

        // 2. VALIDASI DEVICE
        if ($ipAddress && $userAgent) {
            $deviceValidation = $this->validasiDevice(
                $karyawan->id,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            if (!$deviceValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $deviceValidation['message']
                ];
            }
        }

        // 3. CEK CUTI
        $cutiAktif = $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        if ($cutiAktif) {
            return [
                'success' => false,
                'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."
            ];
        }

        // 4. VALIDASI LOKASI
        $lokasi = LokasiAbsensi::where('id', $lokasiId)
            ->where('status', 'aktif')
            ->first();

        if (!$lokasi) {
            return [
                'success' => false,
                'message' => 'Lokasi tidak ditemukan'
            ];
        }

        $validasiLokasi = $this->validasiLokasi($latitude, $longitude, $lokasi);

        if (!$validasiLokasi['valid']) {
            return [
                'success' => false,
                'message' => "Anda berada {$validasiLokasi['jarak']} meter dari lokasi. Radius maksimal {$lokasi->radius} meter."
            ];
        }

        // 5. CEK ABSENSI MASUK
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
                'message' => 'Anda sudah absen pulang pada jam ' .
                    Carbon::parse($absensi->jam_pulang)->format('H:i') . ' WITA'
            ];
        }

        // 6. VALIDASI WAKTU PULANG
        $batasPulang = Carbon::parse($absensi->jamKerja->batas_pulang, 'Asia/Makassar');

        $statusPulang = $now->lessThan($batasPulang) ? 'pulang_cepat' : 'tepat_waktu';

        // 7. SIMPAN PULANG
        try {
            $absensi->update([
                'jam_pulang' => $now->toTimeString(),
                'latitude_pulang' => $latitude,
                'longitude_pulang' => $longitude,
                'jarak_pulang' => $validasiLokasi['jarak'],
                'status_pulang' => $statusPulang,
                'ip_address_pulang' => $ipAddress,
                'user_agent_pulang' => $userAgent
            ]);

            Log::info('Absensi Pulang', [
                'nip' => $nip,
                'nama' => $karyawan->name,
                'waktu' => $now->toDateTimeString(),
                'status' => $statusPulang,
                'ip' => $ipAddress
            ]);

            return [
                'success' => true,
                'message' => $statusPulang === 'tepat_waktu'
                    ? 'Absensi pulang berhasil! Terima kasih atas kerja keras Anda.'
                    : 'Absensi pulang berhasil! Namun Anda pulang lebih awal.',
                'data' => [
                    'nama' => $karyawan->name,
                    'nip' => $nip,
                    'jam_masuk' => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                    'jam_pulang' => $now->format('H:i:s'),
                    'status' => $statusPulang,
                    'jarak' => $validasiLokasi['jarak'] . ' meter',
                    'durasi_kerja' => $this->hitungDurasiKerja(
                        $absensi->jam_masuk,
                        $now->toTimeString()
                    )
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error simpan pulang', [
                'nip' => $nip,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ];
        }
    }

    /**
     * Hitung durasi kerja
     */
    private function hitungDurasiKerja($jamMasuk, $jamPulang)
    {
        try {
            $masuk = Carbon::parse($jamMasuk, 'Asia/Makassar');
            $pulang = Carbon::parse($jamPulang, 'Asia/Makassar');

            $diff = $masuk->diff($pulang);

            return "{$diff->h} jam {$diff->i} menit";
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * GET RIWAYAT ABSENSI
     */
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
        } else {
            $query->whereMonth('tanggal', now()->month)
                  ->whereYear('tanggal', now()->year);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get()->map(function($item) {
            return [
                'tanggal' => $item->tanggal,
                'hari' => Carbon::parse($item->tanggal)->locale('id')->dayName,
                'jam_masuk' => $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : null,
                'jam_pulang' => $item->jam_pulang ? Carbon::parse($item->jam_pulang)->format('H:i') : null,
                'status_masuk' => $item->status_masuk,
                'status_pulang' => $item->status_pulang,
                'jarak_masuk' => $item->jarak_masuk ? round($item->jarak_masuk) : null,
                'jarak_pulang' => $item->jarak_pulang ? round($item->jarak_pulang) : null,
                'lokasi' => $item->lokasiAbsensi?->nama_lokasi ?? '-',
                'durasi_kerja' => ($item->jam_masuk && $item->jam_pulang)
                    ? $this->hitungDurasiKerja($item->jam_masuk, $item->jam_pulang)
                    : '-'
            ];
        });

        try {
            $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);
        } catch (\Exception $e) {
            $jenisPegawai = '-';
        }

        return [
            'success' => true,
            'data' => [
                'pegawai' => [
                    'nama' => $karyawan->name,
                    'nip' => $nip,
                    'jabatan' => $karyawan->user?->roles?->first()?->name ?? '-',
                    'jenis_pegawai' => $jenisPegawai
                ],
                'riwayat' => $riwayat
            ]
        ];
    }
}
