<?php

namespace App\Http\Controllers\Api\V2\Absensi;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\AttendanceHistoryResource;
use App\Models\Absensi;
use App\Services\AbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * CHECK-IN (ABSENSI MASUK)
     * POST /api/v2/attendance/check-in
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Request Body:
     * {
     *   "latitude": -0.504107,
     *   "longitude": 117.153595,
     *   "lokasi_id": 1,
     *   "device_id": "optional_device_id",
     *   "user_agent": "optional_user_agent",
     *   "ip_address": "optional_ip_address"
     * }
     * 
     * Response (Success):
     * {
     *   "success": true,
     *   "message": "Absensi masuk berhasil! Anda tepat waktu.",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso",
     *     "nip": "123456789012345678",
     *     "jam_masuk": "07:15:32",
     *     "jam_kerja": "07:30",
     *     "status": "tepat_waktu",
     *     "device": "Android Phone",
     *     "is_new_device": false,
     *     "area": "Kantor Utama",
     *     "lokasi": "Ruang Guru Lantai 1",
     *     "jarak": "12 meter"
     *   }
     * }
     */
    public function checkIn(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user || !$user->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau bukan guru'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'lokasi_id' => 'nullable|integer|exists:lokasi_absensi,id',
                'device_id' => 'nullable|string',
                'user_agent' => 'nullable|string',
                'ip_address' => 'nullable|ip'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $lokasiId = $request->input('lokasi_id', 1);
            $deviceId = $request->input('device_id');
            $userAgent = $request->input('user_agent') ?? $request->header('User-Agent');
            $ipAddress = $request->input('ip_address') ?? $request->ip();

            // Call AbsensiService
            $result = $this->absensiService->absenMasuk(
                $user->id,
                $latitude,
                $longitude,
                $lokasiId,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'ABSENSI_FAILED'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Check-in error: ' . $e->getMessage(), [
                'user_id' => Auth::guard('api')->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan absensi masuk'
            ], 500);
        }
    }

    /**
     * CHECK-OUT (ABSENSI PULANG)
     * POST /api/v2/attendance/check-out
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Request Body:
     * {
     *   "latitude": -0.504107,
     *   "longitude": 117.153595,
     *   "lokasi_id": 1,
     *   "device_id": "optional_device_id",
     *   "user_agent": "optional_user_agent",
     *   "ip_address": "optional_ip_address"
     * }
     * 
     * Response (Success):
     * {
     *   "success": true,
     *   "message": "Absensi pulang berhasil! Terima kasih atas kerja keras Anda.",
     *   "data": {
     *     "nama": "Budi Santoso",
     *     "nip": "123456789012345678",
     *     "jam_masuk": "07:15:32",
     *     "jam_pulang": "15:45:12",
     *     "status": "tepat_waktu",
     *     "durasi_kerja": "8 jam 30 menit",
     *     "jarak": "15 meter"
     *   }
     * }
     */
    public function checkOut(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user || !$user->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau bukan guru'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'lokasi_id' => 'nullable|integer|exists:lokasi_absensi,id',
                'device_id' => 'nullable|string',
                'user_agent' => 'nullable|string',
                'ip_address' => 'nullable|ip'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $lokasiId = $request->input('lokasi_id', 1);
            $deviceId = $request->input('device_id');
            $userAgent = $request->input('user_agent') ?? $request->header('User-Agent');
            $ipAddress = $request->input('ip_address') ?? $request->ip();

            // Call AbsensiService
            $result = $this->absensiService->absenPulang(
                $user->id,
                $latitude,
                $longitude,
                $lokasiId,
                $ipAddress,
                $userAgent,
                $deviceId
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'ABSENSI_FAILED'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Check-out error: ' . $e->getMessage(), [
                'user_id' => Auth::guard('api')->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan absensi pulang'
            ], 500);
        }
    }

    /**
     * GET TODAY'S ATTENDANCE STATUS
     * GET /api/v2/attendance/today
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Status absensi hari ini",
     *   "data": {
     *     "tanggal": "2025-02-06",
     *     "hari": "Kamis",
     *     "jam_masuk": "07:15:32",
     *     "jam_pulang": "15:45:12",
     *     "status_masuk": "tepat_waktu",
     *     "status_pulang": "tepat_waktu",
     *     "durasi_kerja": "8 jam 30 menit",
     *     "has_checked_in": true,
     *     "has_checked_out": true,
     *     "jarak_masuk": 12,
     *     "jarak_pulang": 15
     *   }
     * }
     */
    public function getToday(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user || !$user->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau bukan guru'
                ], 401);
            }

            $today = Carbon::now('Asia/Makassar')->toDateString();

            $attendance = Absensi::where('karyawan_id', $user->karyawan->id)
                ->where('tanggal', $today)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada absensi hari ini',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status absensi hari ini',
                'data' => new AttendanceResource($attendance)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get today attendance error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * GET ATTENDANCE HISTORY
     * GET /api/v2/attendance/history?bulan=2&tahun=2025&status=semua
     * 
     * Query Parameters:
     * - bulan: integer (1-12), optional (default: bulan current)
     * - tahun: integer, optional (default: tahun current)
     * - status: "semua" | "hadir" | "terlambat" | "alpha", optional (default: semua)
     * - page: integer, optional (default: 1)
     * - per_page: integer (1-100), optional (default: 15)
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Riwayat absensi",
     *   "data": {
     *     "pegawai": {
     *       "nama": "Budi Santoso",
     *       "nip": "123456789012345678",
     *       "jabatan": "guru",
     *       "jenis_pegawai": "guru"
     *     },
     *     "periode": {
     *       "bulan": "Februari",
     *       "tahun": 2025,
     *       "total_hari_kerja": 20,
     *       "total_hadir": 19,
     *       "total_terlambat": 1,
     *       "total_alpha": 0
     *     },
     *     "riwayat": [
     *       {
     *         "tanggal": "2025-02-06",
     *         "hari": "Kamis",
     *         "jam_masuk": "07:15",
     *         "jam_pulang": "15:45",
     *         "status_masuk": "tepat_waktu",
     *         "status_pulang": "tepat_waktu",
     *         "jarak_masuk": 12,
     *         "jarak_pulang": 15,
     *         "lokasi": "Ruang Guru Lantai 1",
     *         "durasi_kerja": "8 jam 30 menit"
     *       }
     *     ],
     *     "pagination": {
     *       "total": 20,
     *       "per_page": 15,
     *       "current_page": 1,
     *       "last_page": 2
     *     }
     *   }
     * }
     */
    public function getHistory(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user || !$user->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau bukan guru'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'bulan' => 'nullable|integer|between:1,12',
                'tahun' => 'nullable|integer|min:2020',
                'status' => 'nullable|in:semua,hadir,terlambat,alpha',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|between:1,100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bulan = $request->input('bulan') ?? Carbon::now('Asia/Makassar')->month;
            $tahun = $request->input('tahun') ?? Carbon::now('Asia/Makassar')->year;
            $status = $request->input('status', 'semua');
            $perPage = $request->input('per_page', 15);

            // Call AbsensiService to get history
            $result = $this->absensiService->getRiwayatAbsensi($user->id, $bulan, $tahun);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $riwayat = $result['data']['riwayat'];

            // Filter by status
            if ($status !== 'semua') {
                $riwayat = collect($riwayat)->filter(function ($item) use ($status) {
                    if ($status === 'terlambat') {
                        return $item['status_masuk'] === 'terlambat';
                    } elseif ($status === 'hadir') {
                        return $item['jam_masuk'] !== null;
                    } elseif ($status === 'alpha') {
                        return $item['jam_masuk'] === null;
                    }
                    return true;
                })->values()->all();
            }

            // Paginate manually
            $total = count($riwayat);
            $currentPage = $request->input('page', 1);
            $lastPage = ceil($total / $perPage);
            $riwayatPaginated = array_slice($riwayat, ($currentPage - 1) * $perPage, $perPage);

            // Calculate statistics
            $hadir = collect($riwayat)->filter(fn($item) => $item['jam_masuk'] !== null)->count();
            $terlambat = collect($riwayat)->filter(fn($item) => $item['status_masuk'] === 'terlambat')->count();
            $alpha = collect($riwayat)->filter(fn($item) => $item['jam_masuk'] === null)->count();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat absensi',
                'data' => [
                    'pegawai' => $result['data']['pegawai'],
                    'periode' => [
                        'bulan' => Carbon::now()->setMonth($bulan)->locale('id')->getTranslatedMonthName('%B'),
                        'tahun' => $tahun,
                        'total_hari_kerja' => count($riwayat),
                        'total_hadir' => $hadir,
                        'total_terlambat' => $terlambat,
                        'total_alpha' => $alpha
                    ],
                    'riwayat' => $riwayatPaginated,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $currentPage,
                        'last_page' => $lastPage
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get history error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat absensi'
            ], 500);
        }
    }

    /**
     * GET ATTENDANCE DETAIL
     * GET /api/v2/attendance/{id}
     * 
     * Headers: Authorization: Bearer {token}
     */
    public function show($id)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user || !$user->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau bukan guru'
                ], 401);
            }

            $attendance = Absensi::where('id', $id)
                ->where('karyawan_id', $user->karyawan->id)
                ->with(['lokasiAbsensi', 'jamKerja'])
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail absensi',
                'data' => new AttendanceResource($attendance)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get attendance detail error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }
}

?>