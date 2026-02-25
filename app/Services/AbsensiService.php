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

class AbsensiService
{
    protected $kmlService;

    public function __construct(KmlService $kmlService)
    {
        $this->kmlService = $kmlService;
    }

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
     * Validasi lokasi - Support KML dan Radius
     */
    public function validasiLokasi($latitude, $longitude, LokasiAbsensi $lokasi = null)
    {
        if (config('absensi.use_kml', false)) {
            return $this->validasiLokasiKml($latitude, $longitude);
        }

        if ($lokasi) {
            return $this->validasiLokasiRadius($latitude, $longitude, $lokasi);
        }

        return [
            'valid' => false,
            'message' => 'Tidak ada metode validasi lokasi yang dikonfigurasi'
        ];
    }

    /**
     * Validasi lokasi menggunakan KML
     */
    private function validasiLokasiKml($latitude, $longitude)
    {
        $result = $this->kmlService->validateLocation($latitude, $longitude);

        if (!$result['valid']) {
            return [
                'valid' => false,
                'message' => $result['message'],
                'jarak' => null
            ];
        }

        return [
            'valid' => true,
            'message' => $result['message'],
            'area_name' => $result['area_name'] ?? 'Area Valid',
            'jarak' => null
        ];
    }

    /**
     * Validasi lokasi menggunakan radius
     */
    private function validasiLokasiRadius($latitude, $longitude, LokasiAbsensi $lokasi)
    {
        $jarak = $this->hitungJarak(
            $latitude,
            $longitude,
            $lokasi->latitude,
            $lokasi->longitude
        );

        return [
            'valid' => $jarak <= $lokasi->radius,
            'jarak' => round($jarak, 2),
            'message' => $jarak <= $lokasi->radius
                ? 'Lokasi valid'
                : "Anda berada {$jarak} meter dari lokasi. Radius maksimal {$lokasi->radius} meter."
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

        $device = DeviceAbsensi::where('karyawan_id', $karyawanId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if (!$device) {
            return $this->registerNewDevice($karyawanId, $fingerprint, $ipAddress, $userAgent, $deviceId);
        }

        return $this->validateExistingDevice($device, $ipAddress);
    }

    /**
     * Register device baru
     */
    private function registerNewDevice($karyawanId, $fingerprint, $ipAddress, $userAgent, $deviceId)
    {
        $existingDevices = DeviceAbsensi::where('karyawan_id', $karyawanId)
            ->where('is_active', true)
            ->count();

        if ($existingDevices >= 3) {
            return [
                'valid' => false,
                'message' => 'Anda sudah mendaftarkan maksimal 3 device. Silahkan hubungi admin untuk menghapus device lama.',
                'device' => null
            ];
        }

        $device = DeviceAbsensi::create([
            'karyawan_id'        => $karyawanId,
            'device_fingerprint' => $fingerprint,
            'device_name'        => $this->parseDeviceName($userAgent),
            'ip_address'         => $ipAddress,
            'user_agent'         => $userAgent,
            'device_id'          => $deviceId,
            'is_active'          => true,
            'last_used_at'       => now(),
            'registered_at'      => now()
        ]);

        Log::info('Device baru terdaftar', [
            'karyawan_id' => $karyawanId,
            'device_name' => $device->device_name,
            'ip'          => $ipAddress
        ]);

        return [
            'valid'         => true,
            'message'       => 'Device baru berhasil didaftarkan',
            'device'        => $device,
            'is_new_device' => true
        ];
    }

    /**
     * Validate existing device
     */
    private function validateExistingDevice(DeviceAbsensi $device, $ipAddress)
    {
        if (!$device->is_active) {
            return [
                'valid'   => false,
                'message' => 'Device Anda telah dinonaktifkan. Silahkan hubungi admin.',
                'device'  => $device
            ];
        }

        $ipChanged = false;
        if ($device->ip_address !== $ipAddress) {
            $ipChanged = true;

            $device->update([
                'ip_address'   => $ipAddress,
                'last_used_at' => now()
            ]);

            Log::warning('IP Address berubah', [
                'karyawan_id' => $device->karyawan_id,
                'old_ip'      => $device->ip_address,
                'new_ip'      => $ipAddress
            ]);
        } else {
            $device->touch('last_used_at');
        }

        return [
            'valid'         => true,
            'message'       => $ipChanged ? 'Perhatian: IP Address Anda berbeda dari terakhir kali' : 'Device terverifikasi',
            'device'        => $device,
            'is_new_device' => false,
            'ip_changed'    => $ipChanged
        ];
    }

    /**
     * Deteksi abuse/manipulasi
     */
    public function deteksiAbuse($karyawanId, $ipAddress)
    {
        $now = Carbon::now('Asia/Makassar');

        $sameIpCount = Absensi::where('ip_address', $ipAddress)
            ->where('tanggal', $now->toDateString())
            ->distinct('karyawan_id')
            ->count('karyawan_id');

        if ($sameIpCount > 5) {
            Log::warning('IP Sharing Detected', [
                'ip'          => $ipAddress,
                'total_users' => $sameIpCount,
                'date'        => $now->toDateString()
            ]);

            return [
                'suspicious' => true,
                'reason'     => 'IP Address ini digunakan oleh banyak karyawan. Harap gunakan device pribadi.'
            ];
        }

        return ['suspicious' => false, 'reason' => null];
    }

    /**
     * Get jam kerja
     */
    public function getJamKerja($jenisPegawai, Carbon $tanggal = null)
    {
        $tanggal = $tanggal ?? Carbon::now('Asia/Makassar');
        $hari    = strtolower($tanggal->locale('id')->dayName);

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
     * Get jenis pegawai dari role.
     * Role yang tidak dikenali → 'umum' (bebas absen, tidak dapat poin).
     */
    public function getJenisPegawaiFromRole(Karyawan $karyawan)
    {
        $role = $karyawan->user?->roles?->first();

        if (!$role) {
            return 'umum';
        }

        $roleMap = [
            'guru'              => 'guru',
            'tenaga-pendidikan' => 'tenaga-pendidikan',
            'admin'             => 'tenaga-pendidikan',
            'shadow-teacher'    => 'shadow-teacher',  // ← underscore, sesuai KaryawanSeeder
        ];

        return $roleMap[$role->name] ?? 'umum';
    }

    /**
     * Cek apakah role mendapat batasan jam masuk & poin rp.
     *
     * HANYA 'umum' yang bebas absen kapan saja tanpa konsekuensi.
     * Semua role lain (guru, tenaga-pendidikan, shadow-teacher, dst.)
     * terkena batasan batas_masuk dan aturan poin.
     *
     * Dengan pendekatan ini, role baru apapun yang ditambahkan di masa depan
     * otomatis terbatas tanpa perlu mengubah method ini.
     */
    private function isRoleTerbatas(string $jenisPegawai): bool
    {
        return $jenisPegawai !== 'umum';
    }

    /**
     * Cek apakah role mendapat poin rp_masuk / rp_pulang.
     *
     * Hanya guru dan tenaga-pendidikan yang mendapat poin.
     * shadow-teacher terbatas jam tapi TIDAK dapat poin.
     * umum bebas jam dan TIDAK dapat poin.
     */
    private function isRoleDapatPoin(string $jenisPegawai): bool
    {
        return in_array($jenisPegawai, ['guru', 'tenaga-pendidikan']);
    }

    /**
     * Get karyawan by user ID
     */
    private function getKaryawanByUserId($userId)
    {
        return Karyawan::where('user_id', $userId)->first();
    }

    /**
     * Validasi common checks
     */
    private function validateCommonChecks($userId, $ipAddress, $userAgent, $deviceId)
    {
        $karyawan = $this->getKaryawanByUserId($userId);

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $now            = Carbon::now('Asia/Makassar');
        $tanggalHariIni = $now->toDateString();

        if ($ipAddress && $userAgent) {
            $deviceValidation = $this->validasiDevice($karyawan->id, $ipAddress, $userAgent, $deviceId);

            if (!$deviceValidation['valid']) {
                return ['success' => false, 'message' => $deviceValidation['message']];
            }

            $abuseCheck = $this->deteksiAbuse($karyawan->id, $ipAddress);
            if ($abuseCheck['suspicious']) {
                Log::warning('Suspicious Activity', [
                    'karyawan_id' => $karyawan->id,
                    'user_id'     => $userId,
                    'ip'          => $ipAddress,
                    'reason'      => $abuseCheck['reason']
                ]);

                return [
                    'success' => false,
                    'message' => 'Aktivitas mencurigakan: ' . $abuseCheck['reason']
                ];
            }
        } else {
            $deviceValidation = null;
        }

        $cutiAktif = $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        if ($cutiAktif) {
            return ['success' => false, 'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."];
        }

        return [
            'success'          => true,
            'karyawan'         => $karyawan,
            'now'              => $now,
            'tanggalHariIni'   => $tanggalHariIni,
            'deviceValidation' => $deviceValidation
        ];
    }

    /**
     * Validasi lokasi absensi
     */
    private function validateLokasiAbsensi($lokasiId, $latitude, $longitude)
    {
        if (config('absensi.use_kml', false)) {
            $validasiLokasi = $this->validasiLokasiKml($latitude, $longitude);

            if (!$validasiLokasi['valid']) {
                return ['success' => false, 'message' => $validasiLokasi['message']];
            }

            return ['success' => true, 'lokasi' => null, 'validasiLokasi' => $validasiLokasi];
        }

        $lokasi = LokasiAbsensi::where('id', $lokasiId)->where('status', 'aktif')->first();

        if (!$lokasi) {
            return ['success' => false, 'message' => 'Lokasi absensi tidak ditemukan atau tidak aktif'];
        }

        $validasiLokasi = $this->validasiLokasiRadius($latitude, $longitude, $lokasi);

        if (!$validasiLokasi['valid']) {
            return ['success' => false, 'message' => $validasiLokasi['message'], 'jarak' => $validasiLokasi['jarak']];
        }

        return ['success' => true, 'lokasi' => $lokasi, 'validasiLokasi' => $validasiLokasi];
    }

    /**
     * ABSEN MASUK
     */
    public function absenMasuk($userId, $latitude, $longitude, $lokasiId = 1, $ipAddress = null, $userAgent = null, $deviceId = null)
    {
        $validation = $this->validateCommonChecks($userId, $ipAddress, $userAgent, $deviceId);
        if (!$validation['success']) return $validation;

        extract($validation);

        $lokasiValidation = $this->validateLokasiAbsensi($lokasiId, $latitude, $longitude);
        if (!$lokasiValidation['success']) return $lokasiValidation;

        $lokasi         = $lokasiValidation['lokasi'];
        $validasiLokasi = $lokasiValidation['validasiLokasi'];

        try {
            $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);
            $jamKerja     = null;

            if ($this->isRoleTerbatas($jenisPegawai)) {
                // Role terbatas: wajib punya jam kerja, hari libur ditolak
                $jamKerja = $this->getJamKerja($jenisPegawai, $now);

                if (is_null($jamKerja->jam_masuk)) {
                    return ['success' => false, 'message' => 'Hari ini adalah hari libur. Absensi tidak diperlukan.'];
                }
            } else {
                // Role umum: jam kerja opsional, tidak memblokir apapun
                try {
                    $jamKerja = $this->getJamKerja($jenisPegawai, $now);
                } catch (\Exception $e) {
                    $jamKerja = null;
                }
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $statusMasuk = 'tepat_waktu';

        if ($this->isRoleTerbatas($jenisPegawai) && $jamKerja) {
            // Role terbatas: lewat batas_masuk → ditolak
            $batasMasuk     = Carbon::parse($jamKerja->batas_masuk, 'Asia/Makassar');
            $jamMasukNormal = Carbon::parse($jamKerja->jam_masuk, 'Asia/Makassar');

            if ($now->greaterThan($batasMasuk)) {
                return [
                    'success' => false,
                    'message' => 'Anda terlambat melewati batas jam masuk (' .
                                 $batasMasuk->format('H:i') . ' WITA). Silahkan hubungi admin.'
                ];
            }

            $statusMasuk = $now->lessThanOrEqualTo($jamMasukNormal) ? 'tepat_waktu' : 'terlambat';
        } elseif ($jamKerja && $jamKerja->jam_masuk) {
            // Role umum: tidak ada penolakan, status dicatat saja
            $jamMasukNormal = Carbon::parse($jamKerja->jam_masuk, 'Asia/Makassar');
            $statusMasuk    = $now->lessThanOrEqualTo($jamMasukNormal) ? 'tepat_waktu' : 'terlambat';
        }

        // Poin hanya untuk role terbatas yang tepat waktu
        $rp_masuk = ($this->isRoleDapatPoin($jenisPegawai) && $statusMasuk === 'tepat_waktu') ? 4000 : 0;

        // Cek duplikasi
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

        try {
            $dataAbsensi = [
                'jam_kerja_id'    => $jamKerja?->id,
                'status_kehadiran' => 'hadir',
                'jam_masuk'       => $now->toTimeString(),
                'latitude_masuk'  => $latitude,
                'longitude_masuk' => $longitude,
                'status_masuk'    => $statusMasuk,
                'ip_address'      => $ipAddress,
                'user_agent'      => $userAgent,
                'device_id'       => $deviceId,
                'rp_masuk'        => $rp_masuk
            ];

            if (!config('absensi.use_kml', false) && $lokasi) {
                $dataAbsensi['lokasi_absensi_id'] = $lokasi->id;
                $dataAbsensi['jarak_masuk']       = $validasiLokasi['jarak'];
            }

            Absensi::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggalHariIni],
                $dataAbsensi
            );

            Log::info('Absensi Masuk', [
                'user_id' => $userId,
                'nama'    => $karyawan->name,
                'waktu'   => $now->toDateTimeString(),
                'status'  => $statusMasuk,
                'role'    => $jenisPegawai,
                'method'  => config('absensi.use_kml') ? 'KML' : 'Radius',
                'ip'      => $ipAddress,
                'device'  => $deviceValidation['device']->device_name ?? 'Unknown'
            ]);

            $responseData = [
                'nama'          => $karyawan->name,
                'jam_masuk'     => $now->format('H:i:s'),
                'jam_kerja'     => $jamKerja ? Carbon::parse($jamKerja->jam_masuk)->format('H:i') : '-',
                'status'        => $statusMasuk,
                'device'        => $deviceValidation['device']->device_name ?? null,
                'is_new_device' => $deviceValidation['is_new_device'] ?? false
            ];

            if (config('absensi.use_kml', false)) {
                $responseData['area'] = $validasiLokasi['area_name'] ?? 'Area Valid';
            } else {
                $responseData['lokasi'] = $lokasi->nama_lokasi ?? '-';
                $responseData['jarak']  = ($validasiLokasi['jarak'] ?? 0) . ' meter';
            }

            return [
                'success' => true,
                'message' => $statusMasuk === 'tepat_waktu'
                    ? 'Absensi masuk berhasil! Anda tepat waktu.'
                    : 'Absensi masuk berhasil! Namun Anda terlambat.',
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('Error simpan absensi masuk', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data absensi.'];
        }
    }

    /**
     * ABSEN PULANG
     */
    public function absenPulang($userId, $latitude, $longitude, $lokasiId = 1, $ipAddress = null, $userAgent = null, $deviceId = null)
    {
        $validation = $this->validateCommonChecks($userId, $ipAddress, $userAgent, $deviceId);
        if (!$validation['success']) return $validation;

        extract($validation);

        $lokasiValidation = $this->validateLokasiAbsensi($lokasiId, $latitude, $longitude);
        if (!$lokasiValidation['success']) return $lokasiValidation;

        $validasiLokasi = $lokasiValidation['validasiLokasi'];

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggalHariIni)
            ->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return ['success' => false, 'message' => 'Anda belum melakukan absensi masuk hari ini'];
        }

        if ($absensi->jam_pulang) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen pulang pada jam ' .
                    Carbon::parse($absensi->jam_pulang)->format('H:i') . ' WITA'
            ];
        }

        $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);
        $statusPulang = 'tepat_waktu';
        $rp_pulang    = 0;

        if ($this->isRoleTerbatas($jenisPegawai) && $absensi->jamKerja) {
            // Role terbatas: pulang cepat tidak dapat poin
            $batasPulang  = Carbon::parse($absensi->jamKerja->batas_pulang, 'Asia/Makassar');
            $statusPulang = $now->lessThan($batasPulang) ? 'pulang_cepat' : 'tepat_waktu';
            $rp_pulang    = ($this->isRoleDapatPoin($jenisPegawai) && $statusPulang === 'tepat_waktu') ? 4000 : 0;
        } elseif ($absensi->jamKerja && $absensi->jamKerja->batas_pulang) {
            // Role umum: status dicatat tapi tidak ada poin & tidak ada pemblokiran
            $batasPulang  = Carbon::parse($absensi->jamKerja->batas_pulang, 'Asia/Makassar');
            $statusPulang = $now->lessThan($batasPulang) ? 'pulang_cepat' : 'tepat_waktu';
        }

        try {
            $dataUpdate = [
                'jam_pulang'        => $now->toTimeString(),
                'latitude_pulang'   => $latitude,
                'longitude_pulang'  => $longitude,
                'status_pulang'     => $statusPulang,
                'ip_address_pulang' => $ipAddress,
                'user_agent_pulang' => $userAgent,
                'rp_pulang'         => $rp_pulang,
            ];

            if (!config('absensi.use_kml', false)) {
                $dataUpdate['jarak_pulang'] = $validasiLokasi['jarak'] ?? 0;
            }

            $absensi->update($dataUpdate);

            Log::info('Absensi Pulang', [
                'user_id' => $userId,
                'nama'    => $karyawan->name,
                'waktu'   => $now->toDateTimeString(),
                'status'  => $statusPulang,
                'role'    => $jenisPegawai,
                'ip'      => $ipAddress
            ]);

            $responseData = [
                'nama'         => $karyawan->name,
                'jam_masuk'    => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                'jam_pulang'   => $now->format('H:i:s'),
                'status'       => $statusPulang,
                'durasi_kerja' => $this->hitungDurasiKerja($absensi->jam_masuk, $now->toTimeString())
            ];

            if (!config('absensi.use_kml', false)) {
                $responseData['jarak'] = ($validasiLokasi['jarak'] ?? 0) . ' meter';
            }

            return [
                'success' => true,
                'message' => $statusPulang === 'tepat_waktu'
                    ? 'Absensi pulang berhasil! Terima kasih atas kerja keras Anda.'
                    : 'Absensi pulang berhasil! Namun Anda pulang lebih awal.',
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('Error simpan pulang', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
    }

    /**
     * Hitung durasi kerja
     */
    private function hitungDurasiKerja($jamMasuk, $jamPulang)
    {
        try {
            $masuk  = Carbon::parse($jamMasuk, 'Asia/Makassar');
            $pulang = Carbon::parse($jamPulang, 'Asia/Makassar');
            $diff   = $masuk->diff($pulang);

            return "{$diff->h} jam {$diff->i} menit";
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * GET RIWAYAT ABSENSI
     */
    public function getRiwayatAbsensi($userId, $bulan = null, $tahun = null)
    {
        $karyawan = $this->getKaryawanByUserId($userId);

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $query = Absensi::where('karyawan_id', $karyawan->id)
            ->with(['lokasiAbsensi', 'jamKerja']);

        if ($bulan && $tahun) {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } else {
            $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get()->map(function ($item) {
            return [
                'tanggal'       => $item->tanggal,
                'hari'          => Carbon::parse($item->tanggal)->locale('id')->dayName,
                'jam_masuk'     => $item->jam_masuk ? Carbon::parse($item->jam_masuk)->format('H:i') : null,
                'jam_pulang'    => $item->jam_pulang ? Carbon::parse($item->jam_pulang)->format('H:i') : null,
                'status_masuk'  => $item->status_masuk,
                'status_pulang' => $item->status_pulang,
                'jarak_masuk'   => $item->jarak_masuk ? round($item->jarak_masuk) : null,
                'jarak_pulang'  => $item->jarak_pulang ? round($item->jarak_pulang) : null,
                'lokasi'        => $item->lokasiAbsensi?->nama_lokasi ?? '-',
                'durasi_kerja'  => ($item->jam_masuk && $item->jam_pulang)
                    ? $this->hitungDurasiKerja($item->jam_masuk, $item->jam_pulang)
                    : '-'
            ];
        });

        $jenisPegawai = $this->getJenisPegawaiFromRole($karyawan);

        return [
            'success' => true,
            'data'    => [
                'pegawai' => [
                    'nama'          => $karyawan->name,
                    'jabatan'       => $karyawan->user?->roles?->first()?->name ?? '-',
                    'jenis_pegawai' => $jenisPegawai
                ],
                'riwayat' => $riwayat
            ]
        ];
    }
}