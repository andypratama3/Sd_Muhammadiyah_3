<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Models\DeviceAbsensi;
use App\Services\KmlService;
use App\Models\LokasiAbsensi;
use App\Services\AbsensiService;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService, KmlService $kmlService)
    {
        $this->absensiService = $absensiService;
        $this->kmlService = $kmlService;
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

    public function getKmlData()
    {
        try {
            $result = $this->kmlService->loadKmlFile();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

            return response()->json([
                'success' => true,
                'polygons' => $result['polygons'],
                'count' => $result['count']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data KML: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses absensi masuk
     */
    public function absenMasuk(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'lokasi_id' => 'nullable|integer',
                'device_id' => 'nullable|string|max:64'
            ]);

            // Ambil user ID dari auth
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }

            // Ambil IP Address dan User Agent
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $deviceId = $request->device_id;

            $result = $this->absensiService->absenMasuk(
                $userId,
                $request->latitude,
                $request->longitude,
                $request->lokasi_id ?? 1,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('Absen Masuk Error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Proses absensi pulang
     */
    public function absenPulang(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'lokasi_id' => 'nullable|integer',
                'device_id' => 'nullable|string|max:64'
            ]);

            // Ambil user ID dari auth
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }

            // Ambil IP Address dan User Agent
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $deviceId = $request->device_id;

            $result = $this->absensiService->absenPulang(
                $userId,
                $request->latitude,
                $request->longitude,
                $request->lokasi_id ?? 1,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            return response()->json($result);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            \Log::error('Absen Pulang Error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Tampilkan riwayat absensi
     */
    public function riwayat(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:' . (date('Y') + 1)
        ]);

        $userId = auth()->id();

        $result = $this->absensiService->getRiwayatAbsensi(
            $userId,
            $request->bulan,
            $request->tahun
        );

        return response()->json($result);
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