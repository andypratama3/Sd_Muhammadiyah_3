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

    // =========================================================================
    // LOKASI
    // =========================================================================

    public function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latFrom     = deg2rad($lat1);
        $lonFrom     = deg2rad($lon1);
        $latTo       = deg2rad($lat2);
        $lonTo       = deg2rad($lon2);
        $latDelta    = $latTo - $latFrom;
        $lonDelta    = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function validasiLokasi($latitude, $longitude, LokasiAbsensi $lokasi = null)
    {
        if (config('absensi.use_kml', false)) {
            return $this->validasiLokasiKml($latitude, $longitude);
        }

        if ($lokasi) {
            return $this->validasiLokasiRadius($latitude, $longitude, $lokasi);
        }

        return ['valid' => false, 'message' => 'Tidak ada metode validasi lokasi yang dikonfigurasi'];
    }

    private function validasiLokasiKml($latitude, $longitude)
    {
        $result = $this->kmlService->validateLocation($latitude, $longitude);

        if (!$result['valid']) {
            return ['valid' => false, 'message' => $result['message'], 'jarak' => null];
        }

        return [
            'valid'     => true,
            'message'   => $result['message'],
            'area_name' => $result['area_name'] ?? 'Area Valid',
            'jarak'     => null
        ];
    }

    private function validasiLokasiRadius($latitude, $longitude, LokasiAbsensi $lokasi)
    {
        $jarak = $this->hitungJarak($latitude, $longitude, $lokasi->latitude, $lokasi->longitude);

        return [
            'valid'   => $jarak <= $lokasi->radius,
            'jarak'   => round($jarak, 2),
            'message' => $jarak <= $lokasi->radius
                ? 'Lokasi valid'
                : "Anda berada {$jarak} meter dari lokasi. Radius maksimal {$lokasi->radius} meter."
        ];
    }

    // =========================================================================
    // DEVICE MANAGEMENT
    // =========================================================================

    /**
     * Generate device fingerprint yang STABIL.
     *
     * - Prioritaskan device_id dari frontend (Android ID / identifierForVendor / UUID localStorage).
     * - Fallback: strip angka versi dari User Agent agar stabil antar update browser/OS.
     */
    public function generateDeviceFingerprint($userAgent, $deviceId = null)
    {
        if (!empty($deviceId)) {
            return hash('sha256', $deviceId);
        }

        $stableUA = preg_replace('/[\d]+/', '', $userAgent);
        return hash('sha256', $stableUA);
    }

    private function parseDeviceName($userAgent)
    {
        if (preg_match('/Android/', $userAgent))   return 'Android Phone';
        if (preg_match('/iPhone/', $userAgent))    return 'iPhone';
        if (preg_match('/iPad/', $userAgent))      return 'iPad';
        if (preg_match('/Windows/', $userAgent))   return 'Windows PC';
        if (preg_match('/Macintosh/', $userAgent)) return 'Mac';
        if (preg_match('/Linux/', $userAgent))     return 'Linux PC';
        return 'Unknown Device';
    }

    /**
     * Validasi device.
     *
     * Prioritas pencarian:
     *   1. device_id  — stabil, dari hardware
     *   2. fingerprint — fallback jika device_id kosong
     *   3. Device baru — daftarkan, maks 3 per karyawan
     */
    public function validasiDevice($karyawanId, $ipAddress, $userAgent, $deviceId = null)
    {
        if (!empty($deviceId)) {
            $device = DeviceAbsensi::where('karyawan_id', $karyawanId)
                ->where('device_id', $deviceId)
                ->first();

            if ($device) {
                $newFingerprint = $this->generateDeviceFingerprint($userAgent, $deviceId);
                if ($device->device_fingerprint !== $newFingerprint) {
                    $device->update(['device_fingerprint' => $newFingerprint]);
                    Log::info('Fingerprint diperbarui (device_id match)', [
                        'karyawan_id' => $karyawanId,
                        'device_id'   => $deviceId,
                    ]);
                }
                return $this->validateExistingDevice($device, $ipAddress);
            }
        }

        $fingerprint = $this->generateDeviceFingerprint($userAgent, $deviceId);
        $device      = DeviceAbsensi::where('karyawan_id', $karyawanId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($device) {
            if (!empty($deviceId) && empty($device->device_id)) {
                $device->update(['device_id' => $deviceId]);
            }
            return $this->validateExistingDevice($device, $ipAddress);
        }

        return $this->registerNewDevice($karyawanId, $fingerprint, $ipAddress, $userAgent, $deviceId);
    }

    private function registerNewDevice($karyawanId, $fingerprint, $ipAddress, $userAgent, $deviceId)
    {
        $existingDevices = DeviceAbsensi::where('karyawan_id', $karyawanId)
            ->where('is_active', true)
            ->count();

        if ($existingDevices >= 3) {
            return [
                'valid'   => false,
                'message' => 'Anda sudah mendaftarkan maksimal 3 device. Silahkan hubungi admin untuk menghapus device lama.',
                'device'  => null
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
            $device->update(['ip_address' => $ipAddress, 'last_used_at' => now()]);
            Log::info('IP Address berubah (normal — ISP dinamis)', [
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

    public function deteksiAbuse($karyawanId, $ipAddress)
    {
        $now         = Carbon::now('Asia/Makassar');
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
            return ['suspicious' => true, 'reason' => 'IP Address ini digunakan oleh banyak karyawan. Harap gunakan device pribadi.'];
        }

        return ['suspicious' => false, 'reason' => null];
    }

    // =========================================================================
    // JAM KERJA & ROLE HELPERS
    // =========================================================================

    public function getJamKerja($jenisPegawai, Carbon $tanggal = null)
    {
        $tanggal  = $tanggal ?? Carbon::now('Asia/Makassar');
        $hari     = strtolower($tanggal->locale('id')->dayName);
        $jamKerja = JamKerja::where('jenis_pegawai', $jenisPegawai)->where('hari', $hari)->first();

        if (!$jamKerja) {
            $jamKerja = JamKerja::where('jenis_pegawai', $jenisPegawai)->where('is_default', true)->first();
        }

        if (!$jamKerja) {
            if (app()->isLocal() || app()->environment('development')) {
                return null;
            }
            throw new \Exception('Jam kerja untuk ' . $jenisPegawai . ' belum dikonfigurasi');
        }

        return $jamKerja;
    }

    public function cekStatusCuti($karyawanId, $tanggal)
    {
        return PengajuanCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->first();
    }

    public function getJenisPegawaiFromRole(Karyawan $karyawan): string
    {
        $role = $karyawan->user?->roles?->first();

        if (!$role) return 'umum';

        $roleMap = [
            'guru'               => 'guru',
            'tenaga-pendidikan'  => 'tenaga-pendidikan',
            'shadow-teacher'     => 'shadow-teacher',
            'admin'              => 'umum',        // ← admin bebas, tidak kena aturan jam
            'superadmin'         => 'umum',        // ← sama
            'umum'               => 'umum',
        ];

        return $roleMap[$role->name] ?? 'umum';
    }

    /**
     * Role terbatas: ada pembatasan jam masuk & blokir lewat batas_masuk.
     * guru, tenaga-pendidikan, shadow-teacher → terbatas.
     * umum → tidak terbatas, bebas absen kapan saja.
     */
    private function isRoleTerbatas(string $jenisPegawai): bool
    {
        return $jenisPegawai !== 'umum';
    }

    /**
     * Role yang mendapat poin rp_masuk / rp_pulang.
     * Hanya guru dan tenaga-pendidikan.
     * shadow-teacher dan umum tidak mendapat poin.
     */
    private function isRoleDapatPoin(string $jenisPegawai): bool
    {
        return in_array($jenisPegawai, ['guru', 'tenaga-pendidikan']);
    }

    /**
     * Hitung rp_masuk berdasarkan role dan waktu absen.
     *
     * Aturan per role:
     * ┌──────────────────┬────────────────────────────────────────────────────┐
     * │ guru             │ Dapat 4000 jika now ≤ batas_masuk (07:00)         │
     * │                  │ Toleransi penuh — selama belum diblokir = dapat   │
     * ├──────────────────┼────────────────────────────────────────────────────┤
     * │ tenaga-pend.     │ Dapat 4000 jika now ≤ jam_masuk (06:45)           │
     * │                  │ Lewat 06:45 = terlambat, rp = 0                  │
     * │                  │ Masih bisa absen sampai batas_masuk (07:00)       │
     * ├──────────────────┼────────────────────────────────────────────────────┤
     * │ shadow-teacher   │ Selalu 0 — tidak mendapat poin                    │
     * ├──────────────────┼────────────────────────────────────────────────────┤
     * │ umum             │ Selalu 0 — tidak mendapat poin                    │
     * └──────────────────┴────────────────────────────────────────────────────┘
     *
     * Catatan: method ini hanya dipanggil jika isRoleDapatPoin() = true,
     * sehingga shadow dan umum tidak akan masuk ke sini.
     */
    private function hitungRpMasuk(string $jenisPegawai, Carbon $now, ?JamKerja $jamKerja): int
    {
        if ($jenisPegawai === 'guru') {
            // Guru: dapat 4000 selama masih lolos batas_masuk.
            // Sudah pasti lolos karena cek batas_masuk dilakukan sebelumnya.
            return 4000;
        }

        if ($jenisPegawai === 'tenaga-pendidikan') {
            // Tendik: batas dapat poin adalah jam_masuk (06:45).
            // Lewat 06:45 → rp = 0, tapi masih bisa absen sampai batas_masuk (07:00).
            $jamMasuk = Carbon::parse($jamKerja->jam_masuk, 'Asia/Makassar');
            return $now->lessThanOrEqualTo($jamMasuk) ? 4000 : 0;
        }

        return 0;
    }

    /**
     * Tentukan status_masuk berdasarkan role dan waktu absen.
     *
     * - guru      : selalu tepat_waktu jika lolos batas_masuk
     * - tendik    : tepat_waktu jika ≤ jam_masuk (06:45), terlambat jika 06:45–07:00
     * - shadow    : selalu tepat_waktu jika lolos batas_masuk
     * - umum      : selalu tepat_waktu (tidak ada pembatasan)
     */
    private function hitungStatusMasuk(string $jenisPegawai, Carbon $now, ?JamKerja $jamKerja): string
    {
        if ($jenisPegawai === 'tenaga-pendidikan' && $jamKerja) {
            $jamMasuk = Carbon::parse($jamKerja->jam_masuk, 'Asia/Makassar');
            return $now->lessThanOrEqualTo($jamMasuk) ? 'tepat_waktu' : 'terlambat';
        }

        return 'tepat_waktu';
    }

    private function getKaryawanByUserId($userId)
    {
        return Karyawan::where('user_id', $userId)->first();
    }

    // =========================================================================
    // COMMON VALIDATION
    // =========================================================================

    /**
     * ⚠️ Cara memanggil dari Controller:
     *
     *   $service->absenMasuk(
     *       auth()->id(),
     *       $request->latitude,
     *       $request->longitude,
     *       $request->lokasi_id ?? 1,
     *       $request->ip(),        // dari HTTP header
     *       $request->userAgent(), // dari HTTP header
     *       $request->device_id    // dari body JSON
     *   );
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
        } else {
            $deviceValidation = null;
        }

        $this->cekStatusCuti($karyawan->id, $tanggalHariIni);
        // if ($cutiAktif) {
        //     return ['success' => false, 'message' => "Anda sedang {$cutiAktif->jenis} pada tanggal ini."];
        // }

        return [
            'success'          => true,
            'karyawan'         => $karyawan,
            'now'              => $now,
            'tanggalHariIni'   => $tanggalHariIni,
            'deviceValidation' => $deviceValidation
        ];
    }

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
            return [
                'success' => false,
                'message' => $validasiLokasi['message'],
                'jarak'   => $validasiLokasi['jarak']
            ];
        }

        return ['success' => true, 'lokasi' => $lokasi, 'validasiLokasi' => $validasiLokasi];
    }

    // =========================================================================
    // ABSEN MASUK
    // =========================================================================

    public function absenMasuk(
        $userId,
        $latitude,
        $longitude,
        $lokasiId  = 1,
        $ipAddress = null,
        $userAgent = null,
        $deviceId  = null
    ) {
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
                $jamKerja = $this->getJamKerja($jenisPegawai, $now);

                // if (!$jamKerja || is_null($jamKerja->jam_masuk) || $jamKerja->jam_masuk === '00:02:00') {
                //     return ['success' => false, 'message' => 'Hari ini adalah hari libur. Absensi tidak diperlukan.'];
                // }

                // Semua role terbatas: lewat batas_masuk (07:00) → blokir total
                $batasMasuk = Carbon::parse($jamKerja->batas_masuk, 'Asia/Makassar');
                if ($now->greaterThan($batasMasuk)) {
                    return [
                        'success' => false,
                        'message' => 'Anda terlambat melewati batas jam masuk (' .
                                     $batasMasuk->format('H:i') . ' WITA). Silahkan hubungi admin.'
                    ];
                }
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        // Hitung status dan rp berdasarkan rule masing-masing role
        $statusMasuk = $this->hitungStatusMasuk($jenisPegawai, $now, $jamKerja);
        $rp_masuk    = $this->isRoleDapatPoin($jenisPegawai)
            ? $this->hitungRpMasuk($jenisPegawai, $now, $jamKerja)
            : 0;

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
                'jam_kerja_id'     => $jamKerja?->id,
                'status_kehadiran' => 'hadir',
                'jam_masuk'        => $now->toTimeString(),
                'latitude_masuk'   => $latitude,
                'longitude_masuk'  => $longitude,
                'status_masuk'     => $statusMasuk,
                'ip_address'       => $ipAddress,
                'user_agent'       => $userAgent,
                'device_id'        => $deviceId,
                'rp_masuk'         => $rp_masuk
            ];

            if (!config('absensi.use_kml', false) && $lokasi) {
                $dataAbsensi['lokasi_absensi_id'] = $lokasi->id ?? null;
                $dataAbsensi['jarak_masuk']       = $validasiLokasi['jarak'] ?? null;
            }

            Absensi::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggalHariIni],
                $dataAbsensi
            );

            $deviceName  = $deviceValidation ? ($deviceValidation['device']->device_name ?? 'Unknown') : 'Unknown';
            $isNewDevice = $deviceValidation ? ($deviceValidation['is_new_device'] ?? false) : false;

            Log::info('Absensi Masuk', [
                'user_id'      => $userId,
                'nama'         => $karyawan->name,
                'waktu'        => $now->toDateTimeString(),
                'role'         => $jenisPegawai,
                'status_masuk' => $statusMasuk,
                'rp_masuk'     => $rp_masuk,
                'method'       => config('absensi.use_kml') ? 'KML' : 'Radius',
                'ip'           => $ipAddress,
                'device'       => $deviceName
            ]);

            $responseData = [
                'nama'          => $karyawan->name,
                'jam_masuk'     => $now->format('H:i:s'),
                'jam_kerja'     => $jamKerja ? Carbon::parse($jamKerja->jam_masuk)->format('H:i') : '-',
                'batas_masuk'   => $jamKerja ? Carbon::parse($jamKerja->batas_masuk)->format('H:i') : '-',
                'status'        => $statusMasuk,
                'rp_masuk'      => $rp_masuk,
                'device'        => $deviceName,
                'is_new_device' => $isNewDevice
            ];

            if (config('absensi.use_kml', false)) {
                $responseData['area'] = $validasiLokasi['area_name'] ?? 'Area Valid';
            } else {
                $responseData['lokasi'] = $lokasi->nama_lokasi ?? '-';
                $responseData['jarak']  = ($validasiLokasi['jarak'] ?? 0) . ' meter';
            }

            $message = match(true) {
                $statusMasuk === 'tepat_waktu' && $rp_masuk > 0
                    => 'Absensi masuk berhasil! Anda tepat waktu dan mendapat Rp ' . number_format($rp_masuk) . '.',
                $statusMasuk === 'terlambat'
                    => 'Absensi masuk berhasil! Namun Anda terlambat, tidak mendapat poin hari ini.',
                default
                    => 'Absensi masuk berhasil!',
            };

            return ['success' => true, 'message' => $message, 'data' => $responseData];

        } catch (\Exception $e) {
            Log::error('Error simpan absensi masuk', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data absensi.'];
        }
    }

    // =========================================================================
    // ABSEN PULANG
    // =========================================================================

    public function absenPulang(
        $userId,
        $latitude,
        $longitude,
        $lokasiId  = 1,
        $ipAddress = null,
        $userAgent = null,
        $deviceId  = null
    ) {
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

        if ($absensi->jamKerja && $absensi->jamKerja->batas_pulang) {
            $batasPulang  = Carbon::parse($absensi->jamKerja->batas_pulang, 'Asia/Makassar');
            $statusPulang = $now->lessThan($batasPulang) ? 'pulang_cepat' : 'tepat_waktu';
            if ((int) ($absensi->rp_masuk ?? 0) > 0) {
                $rp_pulang = ($this->isRoleDapatPoin($jenisPegawai) && $statusPulang === 'tepat_waktu') ? 4000 : 0;
            }
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
                'user_id'       => $userId,
                'nama'          => $karyawan->name,
                'waktu'         => $now->toDateTimeString(),
                'role'          => $jenisPegawai,
                'status_pulang' => $statusPulang,
                'rp_pulang'     => $rp_pulang,
                'ip'            => $ipAddress
            ]);

            $responseData = [
                'nama'         => $karyawan->name,
                'jam_masuk'    => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                'jam_pulang'   => $now->format('H:i:s'),
                'status'       => $statusPulang,
                'rp_pulang'    => $rp_pulang,
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

    // =========================================================================
    // RIWAYAT
    // =========================================================================

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

    public function getRiwayatAbsensi($userId, $bulan = null, $tahun = null)
    {
        $karyawan = $this->getKaryawanByUserId($userId);

        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan'];
        }

        $query = Absensi::where('karyawan_id', $karyawan->id)->with(['lokasiAbsensi', 'jamKerja']);

        if ($bulan && $tahun) {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } else {
            $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);
        }

        $riwayat = $query->orderBy('tanggal', 'desc')->get()->map(function ($item) {
            return [
                'tanggal'       => $item->tanggal,
                'hari'          => Carbon::parse($item->tanggal)->locale('id')->dayName,
                'jam_masuk'     => $item->jam_masuk  ? Carbon::parse($item->jam_masuk)->format('H:i')  : null,
                'jam_pulang'    => $item->jam_pulang ? Carbon::parse($item->jam_pulang)->format('H:i') : null,
                'status_masuk'  => $item->status_masuk,
                'status_pulang' => $item->status_pulang,
                'rp_masuk'      => $item->rp_masuk,
                'rp_pulang'     => $item->rp_pulang,
                'jarak_masuk'   => $item->jarak_masuk  ? round($item->jarak_masuk)  : null,
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
