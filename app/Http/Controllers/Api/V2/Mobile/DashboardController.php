<?php

namespace App\Http\Controllers\Api\V2\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Pembayaran;
use App\Models\Berita;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * MOBILE API - Dashboard Controller
 * 
 * Provides dashboard data for mobile app (KMP/Kotlin Multiplatform)
 * Handles both Guru and Orang Tua roles
 */
class DashboardController extends Controller
{
    /**
     * GET DASHBOARD DATA
     * GET /api/v2/mobile/dashboard
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Response for Guru:
     * {
     *   "success": true,
     *   "message": "Dashboard data",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Budi Santoso",
     *       "nip": "123456789012345678",
     *       "role": "guru",
     *       "email": "budi@example.com",
     *       "photo": "https://..."
     *     },
     *     "attendance_today": {
     *       "has_checked_in": true,
     *       "has_checked_out": false,
     *       "check_in_time": "07:15:32",
     *       "status": "tepat_waktu"
     *     },
     *     "statistics": {
     *       "attendance_this_month": {
     *         "total_days": 20,
     *         "present": 18,
     *         "late": 2,
     *         "absent": 0,
     *         "percentage": 90
     *       }
     *     },
     *     "announcements": [
     *       {
     *         "id": 1,
     *         "title": "Rapat Guru",
     *         "excerpt": "Rapat koordinasi...",
     *         "date": "2025-02-15",
     *         "image": "https://..."
     *       }
     *     ],
     *     "schedule_today": [
     *       {
     *         "id": 1,
     *         "subject": "Matematika",
     *         "class": "5A",
     *         "time_start": "07:30",
     *         "time_end": "09:00",
     *         "room": "Ruang 5A"
     *       }
     *     ]
     *   }
     * }
     * 
     * Response for Siswa/Orang Tua:
     * {
     *   "success": true,
     *   "message": "Dashboard data",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Ahmad",
     *       "nisn": "0012345678",
     *       "role": "siswa",
     *       "email": "ahmad@example.com"
     *     },
     *     "student": {
     *       "id": 1,
     *       "name": "Ahmad",
     *       "nisn": "0012345678",
     *       "class": "5A",
     *       "photo": "https://..."
     *     },
     *     "statistics": {
     *       "payments": {
     *         "total": 12,
     *         "paid": 10,
     *         "unpaid": 2,
     *         "total_amount": 5000000,
     *         "paid_amount": 4000000,
     *         "unpaid_amount": 1000000
     *       }
     *     },
     *     "announcements": [...],
     *     "schedule_today": [...]
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return $this->unauthorized('Harus login terlebih dahulu');
            }

            $role = $user->roles->first()->name ?? null;

            if ($role === 'guru') {
                return $this->getDashboardGuru($user);
            } elseif ($role === 'siswa') {
                return $this->getDashboardSiswa($user);
            }

            return $this->badRequest('Role tidak dikenali');

        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return $this->serverError('Terjadi kesalahan saat mengambil data dashboard');
        }
    }

    /**
     * Get Dashboard Data for Guru
     */
    private function getDashboardGuru($user)
    {
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            return $this->badRequest('Data karyawan tidak ditemukan');
        }

        // Get today's attendance
        $today = Carbon::now('Asia/Makassar')->toDateString();
        $attendanceToday = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $today)
            ->first();

        // Get attendance statistics for current month
        $currentMonth = Carbon::now('Asia/Makassar')->month;
        $currentYear = Carbon::now('Asia/Makassar')->year;
        
        $attendanceStats = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $currentMonth)
            ->whereYear('tanggal', $currentYear)
            ->get();

        $totalDays = $attendanceStats->count();
        $present = $attendanceStats->where('jam_masuk', '!=', null)->count();
        $late = $attendanceStats->where('status_masuk', 'terlambat')->count();
        $absent = $totalDays - $present;
        $percentage = $totalDays > 0 ? round(($present / $totalDays) * 100, 1) : 0;

        // Get latest announcements
        $announcements = Berita::where('status', 'publish')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($berita) {
                return [
                    'id' => $berita->id,
                    'title' => $berita->judul,
                    'excerpt' => \Str::limit(strip_tags($berita->isi), 100),
                    'date' => $berita->created_at->format('Y-m-d'),
                    'image' => $berita->gambar ? asset('storage/' . $berita->gambar) : null,
                    'slug' => $berita->slug
                ];
            });

        // Get today's schedule (if exists)
        $scheduleToday = []; // TODO: Implement if jadwal table has teacher relation

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $karyawan->nama,
                'nip' => $karyawan->nip,
                'role' => 'guru',
                'email' => $karyawan->email,
                'photo' => $karyawan->foto ? asset('storage/' . $karyawan->foto) : null
            ],
            'attendance_today' => $attendanceToday ? [
                'has_checked_in' => $attendanceToday->jam_masuk !== null,
                'has_checked_out' => $attendanceToday->jam_pulang !== null,
                'check_in_time' => $attendanceToday->jam_masuk,
                'check_out_time' => $attendanceToday->jam_pulang,
                'status' => $attendanceToday->status_masuk
            ] : [
                'has_checked_in' => false,
                'has_checked_out' => false,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => null
            ],
            'statistics' => [
                'attendance_this_month' => [
                    'total_days' => $totalDays,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'percentage' => $percentage
                ]
            ],
            'announcements' => $announcements,
            'schedule_today' => $scheduleToday
        ], 'Dashboard data berhasil diambil');
    }

    /**
     * Get Dashboard Data for Siswa
     */
    private function getDashboardSiswa($user)
    {
        // Find student by NISN
        $siswa = Siswa::where('nisn', $user->nisn)->first();

        if (!$siswa) {
            return $this->badRequest('Data siswa tidak ditemukan');
        }

        // Get payment statistics
        $payments = Pembayaran::where('siswa_id', $siswa->id)->get();
        
        $totalPayments = $payments->count();
        $paidPayments = $payments->where('status', 'paid')->count();
        $unpaidPayments = $totalPayments - $paidPayments;
        
        $totalAmount = $payments->sum('nominal');
        $paidAmount = $payments->where('status', 'paid')->sum('nominal');
        $unpaidAmount = $totalAmount - $paidAmount;

        // Get latest announcements
        $announcements = Berita::where('status', 'publish')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($berita) {
                return [
                    'id' => $berita->id,
                    'title' => $berita->judul,
                    'excerpt' => \Str::limit(strip_tags($berita->isi), 100),
                    'date' => $berita->created_at->format('Y-m-d'),
                    'image' => $berita->gambar ? asset('storage/' . $berita->gambar) : null,
                    'slug' => $berita->slug
                ];
            });

        // Get today's schedule
        $scheduleToday = []; // TODO: Implement based on student's class

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $siswa->nama_lengkap ?? $siswa->nama,
                'nisn' => $siswa->nisn,
                'role' => 'siswa',
                'email' => $user->email
            ],
            'student' => [
                'id' => $siswa->id,
                'name' => $siswa->nama_lengkap ?? $siswa->nama,
                'nisn' => $siswa->nisn,
                'class' => $siswa->kelas->nama ?? null,
                'photo' => $siswa->foto ? asset('storage/' . $siswa->foto) : null
            ],
            'statistics' => [
                'payments' => [
                    'total' => $totalPayments,
                    'paid' => $paidPayments,
                    'unpaid' => $unpaidPayments,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'unpaid_amount' => $unpaidAmount
                ]
            ],
            'announcements' => $announcements,
            'schedule_today' => $scheduleToday
        ], 'Dashboard data berhasil diambil');
    }
}
