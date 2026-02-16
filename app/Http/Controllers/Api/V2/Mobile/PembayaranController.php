<?php

namespace App\Http\Controllers\Api\V2\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\JudulPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * MOBILE API - Pembayaran Controller
 * 
 * Handles payment data for mobile app (KMP/Kotlin Multiplatform)
 * For Siswa role (students/parents accessing student payment data)
 */
class PembayaranController extends Controller
{
    /**
     * GET PAYMENT LIST
     * GET /api/v2/mobile/pembayaran
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Query Parameters:
     * - status: "all" | "paid" | "unpaid" (default: all)
     * - tahun_ajaran: string (optional, e.g., "2024/2025")
     * - page: integer (default: 1)
     * - per_page: integer (default: 15)
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Data pembayaran",
     *   "data": {
     *     "student": {
     *       "id": 1,
     *       "name": "Ahmad",
     *       "nisn": "0012345678",
     *       "class": "5A"
     *     },
     *     "summary": {
     *       "total_tagihan": 5000000,
     *       "total_dibayar": 4000000,
     *       "total_belum_dibayar": 1000000,
     *       "jumlah_lunas": 10,
     *       "jumlah_belum_lunas": 2
     *     },
     *     "payments": [
     *       {
     *         "id": 1,
     *         "judul": "SPP Januari 2025",
     *         "kategori": "SPP",
     *         "nominal": 500000,
     *         "status": "paid",
     *         "tanggal_jatuh_tempo": "2025-01-10",
     *         "tanggal_bayar": "2025-01-08",
     *         "metode_pembayaran": "Transfer Bank",
     *         "bukti_pembayaran": "https://...",
     *         "is_overdue": false
     *       }
     *     ],
     *     "pagination": {
     *       "total": 12,
     *       "per_page": 15,
     *       "current_page": 1,
     *       "last_page": 1
     *     }
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

            // Verify user is siswa
            $role = $user->roles->first()->name ?? null;
            if ($role !== 'siswa') {
                return $this->forbidden('Akses hanya untuk siswa');
            }

            // Find student
            $siswa = Siswa::where('nisn', $user->nisn)->first();

            if (!$siswa) {
                return $this->badRequest('Data siswa tidak ditemukan');
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:all,paid,unpaid',
                'tahun_ajaran' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|between:1,100'
            ]);

            if ($validator->fails()) {
                return $this->validationError('Validasi gagal', $validator->errors());
            }

            $status = $request->input('status', 'all');
            $tahunAjaran = $request->input('tahun_ajaran');
            $perPage = $request->input('per_page', 15);

            // Query payments
            $query = Pembayaran::where('siswa_id', $siswa->id)
                ->with(['judulPembayaran']);

            if ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($status === 'unpaid') {
                $query->where('status', '!=', 'paid');
            }

            if ($tahunAjaran) {
                $query->whereHas('judulPembayaran', function ($q) use ($tahunAjaran) {
                    $q->where('tahun_ajaran', $tahunAjaran);
                });
            }

            $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Calculate summary
            $allPayments = Pembayaran::where('siswa_id', $siswa->id)->get();
            $totalTagihan = $allPayments->sum('nominal');
            $totalDibayar = $allPayments->where('status', 'paid')->sum('nominal');
            $totalBelumDibayar = $totalTagihan - $totalDibayar;
            $jumlahLunas = $allPayments->where('status', 'paid')->count();
            $jumlahBelumLunas = $allPayments->where('status', '!=', 'paid')->count();

            return $this->success([
                'student' => [
                    'id' => $siswa->id,
                    'name' => $siswa->nama_lengkap ?? $siswa->nama,
                    'nisn' => $siswa->nisn,
                    'class' => $siswa->kelas->nama ?? null
                ],
                'summary' => [
                    'total_tagihan' => $totalTagihan,
                    'total_dibayar' => $totalDibayar,
                    'total_belum_dibayar' => $totalBelumDibayar,
                    'jumlah_lunas' => $jumlahLunas,
                    'jumlah_belum_lunas' => $jumlahBelumLunas
                ],
                'payments' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'judul' => $payment->judulPembayaran->judul ?? 'N/A',
                        'kategori' => $payment->judulPembayaran->kategori ?? 'N/A',
                        'nominal' => $payment->nominal,
                        'status' => $payment->status,
                        'tanggal_jatuh_tempo' => $payment->tanggal_jatuh_tempo,
                        'tanggal_bayar' => $payment->tanggal_bayar,
                        'metode_pembayaran' => $payment->metode_pembayaran,
                        'bukti_pembayaran' => $payment->bukti_pembayaran ? asset('storage/' . $payment->bukti_pembayaran) : null,
                        'is_overdue' => $payment->tanggal_jatuh_tempo && now()->gt($payment->tanggal_jatuh_tempo) && $payment->status !== 'paid'
                    ];
                }),
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage()
                ]
            ], 'Data pembayaran berhasil diambil');

        } catch (\Exception $e) {
            \Log::error('Get payments error: ' . $e->getMessage());
            return $this->serverError('Terjadi kesalahan saat mengambil data pembayaran');
        }
    }

    /**
     * GET PAYMENT DETAIL
     * GET /api/v2/mobile/pembayaran/{id}
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Response:
     * {
     *   "success": true,
     *   "message": "Detail pembayaran",
     *   "data": {
     *     "id": 1,
     *     "judul": "SPP Januari 2025",
     *     "kategori": "SPP",
     *     "deskripsi": "Pembayaran SPP bulan Januari 2025",
     *     "nominal": 500000,
     *     "status": "paid",
     *     "tanggal_jatuh_tempo": "2025-01-10",
     *     "tanggal_bayar": "2025-01-08",
     *     "metode_pembayaran": "Transfer Bank",
     *     "bukti_pembayaran": "https://...",
     *     "catatan": "Pembayaran tepat waktu",
     *     "tahun_ajaran": "2024/2025",
     *     "is_overdue": false,
     *     "student": {
     *       "id": 1,
     *       "name": "Ahmad",
     *       "nisn": "0012345678",
     *       "class": "5A"
     *     }
     *   }
     * }
     */
    public function show(Request $request, $id)
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return $this->unauthorized('Harus login terlebih dahulu');
            }

            // Verify user is siswa
            $role = $user->roles->first()->name ?? null;
            if ($role !== 'siswa') {
                return $this->forbidden('Akses hanya untuk siswa');
            }

            // Find student
            $siswa = Siswa::where('nisn', $user->nisn)->first();

            if (!$siswa) {
                return $this->badRequest('Data siswa tidak ditemukan');
            }

            // Find payment
            $payment = Pembayaran::where('id', $id)
                ->where('siswa_id', $siswa->id)
                ->with(['judulPembayaran'])
                ->first();

            if (!$payment) {
                return $this->notFound('Data pembayaran tidak ditemukan');
            }

            return $this->success([
                'id' => $payment->id,
                'judul' => $payment->judulPembayaran->judul ?? 'N/A',
                'kategori' => $payment->judulPembayaran->kategori ?? 'N/A',
                'deskripsi' => $payment->judulPembayaran->deskripsi ?? null,
                'nominal' => $payment->nominal,
                'status' => $payment->status,
                'tanggal_jatuh_tempo' => $payment->tanggal_jatuh_tempo,
                'tanggal_bayar' => $payment->tanggal_bayar,
                'metode_pembayaran' => $payment->metode_pembayaran,
                'bukti_pembayaran' => $payment->bukti_pembayaran ? asset('storage/' . $payment->bukti_pembayaran) : null,
                'catatan' => $payment->catatan,
                'tahun_ajaran' => $payment->judulPembayaran->tahun_ajaran ?? null,
                'is_overdue' => $payment->tanggal_jatuh_tempo && now()->gt($payment->tanggal_jatuh_tempo) && $payment->status !== 'paid',
                'student' => [
                    'id' => $siswa->id,
                    'name' => $siswa->nama_lengkap ?? $siswa->nama,
                    'nisn' => $siswa->nisn,
                    'class' => $siswa->kelas->nama ?? null
                ]
            ], 'Detail pembayaran berhasil diambil');

        } catch (\Exception $e) {
            \Log::error('Get payment detail error: ' . $e->getMessage());
            return $this->serverError('Terjadi kesalahan saat mengambil detail pembayaran');
        }
    }
}
