<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Models\DeviceAbsensi;
use App\Models\LokasiAbsensi;
use App\Services\AbsensiService;
use App\Http\Controllers\Controller;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * Tampilkan form absensi
     */
    public function index()
    {
        $user = auth()->user();

        // Ambil lokasi aktif (dipakai semua role)
        $lokasi = LokasiAbsensi::where('status', 'aktif')->get();
        $jamKerja = null; // Default untuk admin

        /**
         * ============================
         * SUPERADMIN / ADMIN
         * ============================
         */
        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return view('dashboard.absensis.index', compact('lokasi', 'jamKerja'));
        }

        /**
         * ============================
         * USER BIASA (KARYAWAN)
         * ============================
         */
        $karyawan = Karyawan::where('user_id', $user->id)->first();

        if (!$karyawan) {
            abort(403, 'Data karyawan tidak ditemukan');
        }

        try {
            // jenisPegawai: string → guru / tenaga-pendidikan
            $jenisPegawai = $this->absensiService
                ->getJenisPegawaiFromRole($karyawan);



            // jamKerja: object JamKerja
            $jamKerja = $this->absensiService
                ->getJamKerja($jenisPegawai);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }

        return view('dashboard.absensis.index', compact(
            'lokasi',
            'karyawan',
            'jenisPegawai',
            'jamKerja',
        ));
    }

    /**
     * Proses absensi masuk
     */
    public function absenMasuk(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'lokasi_id' => 'nullable|exists:lokasi_absensi,id',
            'device_id' => 'nullable|string|max:64'
        ]);

        // Ambil IP Address dan User Agent
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $deviceId = $request->device_id;

        $result = $this->absensiService->absenMasuk(
            $request->nip,
            $request->latitude,
            $request->longitude,
            $request->lokasi_id ?? 1,
            $ipAddress,
            $userAgent,
            $deviceId
        );

        return response()->json($result);
    }

    /**
     * Proses absensi pulang
     */
    public function absenPulang(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'lokasi_id' => 'nullable|exists:lokasi_absensi,id',
            'device_id' => 'nullable|string|max:64'
        ]);

        // Ambil IP Address dan User Agent
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $deviceId = $request->device_id;

        $result = $this->absensiService->absenPulang(
            $request->nip,
            $request->latitude,
            $request->longitude,
            $request->lokasi_id ?? 1,
            $ipAddress,      // ← Tambahan IP
            $userAgent,      // ← Tambahan User Agent
            $deviceId        // ← Tambahan Device ID
        );

        return response()->json($result);
    }

    /**
     * Tampilkan riwayat absensi
     */
    public function riwayat(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:' . (date('Y') + 1)
        ]);

        $result = $this->absensiService->getRiwayatAbsensi(
            $request->nip,
            $request->bulan,
            $request->tahun
        );

        return response()->json($result);
    }

    /**
     * Tampilkan daftar device milik user yang sedang login
     */
    public function myDevices()
    {
        $user = auth()->user();
        $karyawan = Karyawan::where('user_id', $user->id)->first();

        if (!$karyawan) {
            abort(403, 'Data karyawan tidak ditemukan');
        }

        $devices = DeviceAbsensi::where('karyawan_id', $karyawan->id)
            ->orderBy('last_used_at', 'desc')
            ->get();

        return view('dashboard.absensis.my-devices', compact('devices', 'karyawan'));
    }

    /**
     * Nonaktifkan device
     * User bisa nonaktifkan device miliknya sendiri
     * Admin bisa nonaktifkan device siapa saja
     */
    public function deactivateDevice(Request $request, $deviceId)
    {
        $device = DeviceAbsensi::findOrFail($deviceId);
        $user = auth()->user();

        // Cek authorization
        if (!$user->hasAnyRole(['superadmin', 'admin'])) {
            // User biasa hanya bisa nonaktifkan device miliknya sendiri
            $karyawan = Karyawan::where('user_id', $user->id)->first();

            if (!$karyawan || $device->karyawan_id !== $karyawan->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menonaktifkan device ini'
                ], 403);
            }
        }

        $device->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device "' . $device->device_name . '" berhasil dinonaktifkan'
        ]);
    }

    /**
     * Aktifkan kembali device yang sudah dinonaktifkan
     * Hanya admin yang bisa
     */
    public function activateDevice(Request $request, $deviceId)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat mengaktifkan device'
            ], 403);
        }

        $device = DeviceAbsensi::findOrFail($deviceId);
        $device->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Device "' . $device->device_name . '" berhasil diaktifkan kembali'
        ]);
    }

    /**
     * Hapus device (Admin only)
     */
    public function deleteDevice(Request $request, $deviceId)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat menghapus device'
            ], 403);
        }

        $device = DeviceAbsensi::findOrFail($deviceId);
        $deviceName = $device->device_name;
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device "' . $deviceName . '" berhasil dihapus'
        ]);
    }

    /**
     * Kelola semua device (Admin only)
     */
    public function manageDevices()
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['superadmin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $devices = DeviceAbsensi::with('karyawan.user')
            ->orderBy('last_used_at', 'desc')
            ->paginate(20);

        return view('dashboard.absensis.manage-devices', compact('devices'));
    }

    /**
     * API: Get device info untuk debugging
     */
    public function getDeviceInfo(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => [
                    'X-Forwarded-For' => $request->header('X-Forwarded-For'),
                    'X-Real-IP' => $request->header('X-Real-IP'),
                ],
                'server_time' => now()->toDateTimeString(),
                'timezone' => config('app.timezone')
            ]
        ]);
    }
}
