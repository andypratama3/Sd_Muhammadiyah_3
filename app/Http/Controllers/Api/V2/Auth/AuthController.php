<?php

namespace App\Http\Controllers\Api\V2\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Absensi;
use Illuminate\Http\Request;
use App\Services\AbsensiService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AttendanceResource;

class AuthController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * LOGIN ENDPOINT
     * POST /api/v2/auth/login
     * 
     * Request:
     * {
     *   "identifier": "123456789012345678" (NIP untuk Guru / NISN untuk Orang Tua),
     *   "password": "password123",
     *   "role": "guru" | "orang-tua",
     *   "device_id": "optional_device_identifier",
     *   "user_agent": "optional_user_agent_string",
     *   "ip_address": "optional_ip_address"
     * }
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
                'password' => 'required|string|min:6',
                'role' => 'required|in:guru,orang-tua',
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

            $identifier = $request->input('identifier');
            $password = $request->input('password');
            $role = $request->input('role');
            $deviceId = $request->input('device_id');
            $userAgent = $request->input('user_agent') ?? $request->header('User-Agent');
            $ipAddress = $request->input('ip_address') ?? $request->ip();

            // Cari user berdasarkan NIP/NISN dan role
            $user = User::with('karyawan', 'roles')
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            })
            ->where(function ($query) use ($identifier, $role) {
                if ($role === 'guru') {
                    $query->whereHas('karyawan', function ($q) use ($identifier) {
                        $q->where('nip', $identifier);
                    });
                } else {
                    $query->where('nisn', $identifier);
                }
            })
            ->first();



            if (!$user || !password_verify($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIP/NISN atau password salah'
                ], 401);
            }



            // Device validation
            if ($userAgent && $ipAddress) {
                $deviceValidation = $this->absensiService->validasiDevice(
                    $user->karyawan?->id ?? $user->id,
                    $ipAddress,
                    $userAgent,
                    $deviceId
                );

                if (!$deviceValidation['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => $deviceValidation['message'],
                        'device_validation' => $deviceValidation
                    ], 403);
                }
            }

            // Generate JWT token
            $token = JwtAuth::fromUser($user);
            
            // Update last login
            $user->update([
                'last_login_at' => Carbon::now('Asia/Makassar'),
                'last_login_ip' => $ipAddress,
                'last_login_device' => $userAgent
            ]);

            $responseData = [
                // 'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'device_registered' => $deviceValidation['is_new_device'] ?? false
            ];

            // Jika guru, tambahkan info attendance hari ini
            if ($role === 'guru' && $user->karyawan) {
                $todayAttendance = Absensi::where('karyawan_id', $user->karyawan->id)
                    ->where('tanggal', Carbon::now('Asia/Makassar')->toDateString())
                    ->first();
                
                // $responseData['today_attendance'] = $todayAttendance ? new AttendanceResource($todayAttendance) : null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login'
            ], 500);
        }
    }

    /**
     * GET CURRENT USER
     * GET /api/v2/auth/me
     * Headers: Authorization: Bearer {token}
     */
    public function me(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $responseData = new UserResource($user);

            // Jika guru, tambahkan info attendance hari ini
            if ($user->karyawan) {
                $todayAttendance = Absensi::where('karyawan_id', $user->karyawan->id)
                    ->where('tanggal', Carbon::now('Asia/Makassar')->toDateString())
                    ->first();
                
                $responseData->additional(['today_attendance' => $todayAttendance ? new AttendanceResource($todayAttendance) : null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data user',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get current user error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * REFRESH TOKEN
     * POST /api/v2/auth/refresh-token
     * Headers: Authorization: Bearer {token}
     */
    public function refreshToken(Request $request)
    {
        try {
            $token = JWTAuth::refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token diperbarui',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Refresh token error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui token'
            ], 401);
        }
    }

    /**
     * LOGOUT
     * POST /api/v2/auth/logout
     * Headers: Authorization: Bearer {token}
     */
    public function logout(Request $request)
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout'
            ], 500);
        }
    }
}

?>