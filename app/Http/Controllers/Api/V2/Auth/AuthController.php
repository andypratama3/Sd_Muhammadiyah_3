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
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password'   => 'required|string|min:6',
            'role'       => 'required|in:guru,siswa',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                'Validasi gagal',
                $validator->errors()
            );
        }

        $user = User::with('roles', 'karyawan')
            ->whereHas('roles', fn ($q) => $q->where('name', $request->role))
            ->where(function ($q) use ($request) {
                if ($request->role === 'guru') {
                    $q->whereHas('karyawan', fn ($x) =>
                        $x->where('email', $request->identifier)
                    );
                } else {
                    $q->where('nisn', $request->identifier);
                }
            })
            ->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return $this->unauthorized('Identifier atau password salah');
        }

        $token = auth('api')->login($user);

        return $this->success([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ], 'Login berhasil');
    }


    /**
     * GET CURRENT USER
     * GET /api/v2/auth/me
     * Headers: Authorization: Bearer {token}
     */
    public function me()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->unauthorized('Harus login terlebih dahulu');
        }

        $role = $user->roles->first()->name;
        $user->role = $role;

        // mapping to Guru Or Siswa 
        if ($role === 'guru') {
            $user->role = 'Guru';
        } else {
            $user->role = 'Siswa';
        }

        return $this->success(
            [
                'user' => $user,
            ],
            'Data user'
        );
    }

    /**
     * REFRESH TOKEN
     * POST /api/v2/auth/refresh-token
     * Headers: Authorization: Bearer {token}
     */
    public function refreshToken()
    {
        try {
            $newToken = auth('api')->refresh();

            return $this->success([
                'access_token' => $newToken,
                'token_type'   => 'Bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
            ], 'Token diperbarui');

        } catch (\Exception $e) {
            return $this->unauthorized('Token tidak valid atau sudah kadaluarsa');
        }
    }



    /**
     * LOGOUT
     * POST /api/v2/auth/logout
     * Headers: Authorization: Bearer {token}
     */
   public function logout()
    {
        try {
            auth('api')->logout();

            return $this->success(
                null,
                'Logout berhasil'
            );
        } catch (\Exception $e) {
            return $this->serverError('Gagal logout');
        }
    }

}
