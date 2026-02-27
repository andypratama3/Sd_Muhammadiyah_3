<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Http\Controllers\Controller;
use App\Models\DeviceAbsensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class DeviceAbsensiController extends Controller
{
    /**
     * List semua karyawan yang punya device, digroup per karyawan
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $karyawans = Karyawan::with(['user.roles', 'devices' => function ($q) {
                $q->orderBy('is_active', 'desc')->orderBy('last_used_at', 'desc');
            }])
            ->whereHas('devices')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.absensis.device.index', compact('karyawans', 'search'));
    }

    /**
     * Get devices milik karyawan tertentu (untuk modal via AJAX)
     */
    public function show($karyawan_id)
    {
        $karyawan = Karyawan::with(['user.roles', 'devices' => function ($q) {
                $q->orderBy('is_active', 'desc')->orderBy('last_used_at', 'desc');
            }])
            ->findOrFail($karyawan_id);

        $devices = DeviceAbsensi::where('karyawan_id', $karyawan->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'success'  => true,
            'karyawan' => [
                'id'     => $karyawan->id,
                'name'   => $karyawan->name,
                'jabatan' => $karyawan->user?->roles?->first()?->name ?? '-',
            ],
            'devices' => $devices->map(fn($d) => [
                'id'          => $d->id,
                'device_name' => $d->device_name,
                'device_id'   => $d->device_id,
                'ip_address'  => $d->ip_address,
                'is_active'   => $d->is_active,
                'last_used'   => $d->last_used_at?->format('d M Y H:i') ?? '-',
                'registered'  => $d->registered_at?->format('d M Y H:i') ?? '-',
                'is_stale'    => $d->isStale(90),
            ]),
        ]);
    }

    /**
     * Toggle aktif/nonaktif device
     */
    public function toggle(DeviceAbsensi $device)
    {
        $device->update(['is_active' => !$device->is_active]);

        $status = $device->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success'   => true,
            'message'   => "Device berhasil {$status}",
            'is_active' => $device->is_active,
        ]);
    }

    /**
     * Hapus satu device
     */
    public function destroy(DeviceAbsensi $device)
    {
        $karyawanName = $device->karyawan->name ?? '-';
        $deviceName   = $device->device_name;

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => "Device {$deviceName} milik {$karyawanName} berhasil dihapus",
        ]);
    }

    /**
     * Reset semua device milik satu karyawan
     * (hapus semua → user daftar ulang saat absen berikutnya)
     */
    public function resetKaryawan($karyawan_id)
    {
        $karyawan = Karyawan::findOrFail($karyawan_id);
        $total = DeviceAbsensi::where('karyawan_id', $karyawan->id)->count();
        DeviceAbsensi::where('karyawan_id', $karyawan->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Semua device ({$total}) milik {$karyawan->name} berhasil direset",
        ]);
    }

    /**
     * Cleanup device stale (tidak dipakai > 90 hari) — bulk action
     */
    public function cleanupStale()
    {
        $total = DeviceAbsensi::where('last_used_at', '<', now()->subDays(90))
            ->orWhereNull('last_used_at')
            ->count();

        DeviceAbsensi::where('last_used_at', '<', now()->subDays(90))
            ->orWhereNull('last_used_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$total} device tidak aktif berhasil dihapus",
        ]);
    }
}
