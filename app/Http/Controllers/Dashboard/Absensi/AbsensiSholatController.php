<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use App\Services\AbsensiSholatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiSholatController extends Controller
{
    public function __construct(protected AbsensiSholatService $service) {}

    /**
     * POST /dashboard/absensis/sholat
     */
    public function absen(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'device_id' => 'nullable|string|max:255',
        ]);

        $result = $this->service->absenSholat(
            userId:    auth()->id(),
            latitude:  $request->latitude,
            longitude: $request->longitude,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            deviceId:  $request->device_id,
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * GET /dashboard/absensis/sholat/status
     */
    public function status(): JsonResponse
    {
        $result = $this->service->getStatusHariIni(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * GET /dashboard/absensis/sholat/riwayat
     */
    public function riwayat(Request $request): JsonResponse
    {
        $result = $this->service->getRiwayat(
            userId: auth()->id(),
            bulan:  $request->query('bulan'),
            tahun:  $request->query('tahun'),
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
