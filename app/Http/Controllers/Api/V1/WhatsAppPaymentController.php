<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Siswa;
use App\Models\Charge;
use App\Models\WaRequestLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

/**
 * WhatsAppPaymentController
 *
 * Endpoint ini dipertahankan sebagai internal API:
 *  POST /api/v2/whatsapp/check-payment
 *
 * Middleware: validate.n8n + wa.ratelimit
 * (Bisa dipanggil dari tools internal / admin dashboard)
 */
class WhatsAppPaymentController extends Controller
{
    /**
     * Cek data pembayaran siswa
     * Validasi: NISN + nomor WA harus match di tabel siswas
     */
    public function checkPayment(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // --- Validasi input ---
            $validated = $request->validate([
                'phone' => ['required', 'string', 'min:10', 'max:20'],
                'nisn'  => ['required', 'string', 'regex:/^\d{10}$/'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'code'    => 'INVALID_INPUT',
                'message' => 'Format input tidak valid',
                'errors'  => $e->errors(),
            ], 422);
        }

        $phone = $validated['phone'];
        $nisn  = $validated['nisn'];

        try {
            // --- Konversi nomor WA ke format lokal (08xxx) ---
            $localPhone = $this->phoneToLocal($phone);

            Log::channel('whatsapp')->info('🔍 Check payment API', [
                'nisn'  => $nisn,
                'phone' => $this->maskPhone($phone),
                'local' => $localPhone,
            ]);

            // --- Cari siswa: NISN + no_hp harus match ---
            $siswa = Siswa::where('nisn', $nisn)
                ->where('no_hp', $localPhone)
                ->whereNull('deleted_at')
                ->with(['kelas' => function ($q) {
                    $q->select('kelas.id', 'kelas.name', 'kelas.grade')
                      ->withPivot('category_kelas');
                }])
                ->first();

            if (!$siswa) {
                $ms = (int) ((microtime(true) - $startTime) * 1000);
                $this->logRequest($phone, $nisn, null, 'not_found', $ms);

                return response()->json([
                    'success' => false,
                    'code'    => 'NOT_FOUND',
                    'message' => 'Data siswa tidak ditemukan. NISN atau nomor WA tidak sesuai.',
                ], 404);
            }

            // --- Ambil kelas aktif ---
            $kelasAktif = $siswa->kelas->first();
            $namaKelas  = $kelasAktif ? ($kelasAktif->name ?? '-') : '-';

            // --- Ambil semua charges ---
            $charges = Charge::where('siswa_id', $siswa->id)
                ->whereNull('deleted_at')
                ->with('kategori_pembayaran')
                ->orderByDesc('created_at')
                ->get();

            $statusLunas = ['settlement', 'pay_offline'];
            $statusBelum = ['pending', 'expired'];

            // Tagihan belum lunas
            $tagihan = $charges->whereIn('transaction_status', $statusBelum)
                ->values()
                ->map(fn($c) => $this->formatTagihanItem($c));

            // Riwayat lunas (5 terakhir)
            $lunas = $charges->whereIn('transaction_status', $statusLunas)
                ->sortByDesc('transaction_time')
                ->take(5)
                ->values()
                ->map(fn($c) => $this->formatLunasItem($c));

            $totalBelumBayar = $charges->whereIn('transaction_status', $statusBelum)
                ->sum('gross_amount');

            $ms = (int) ((microtime(true) - $startTime) * 1000);
            $this->logRequest($phone, $nisn, $siswa->id, 'success', $ms);

            Log::channel('whatsapp')->info('✅ Payment data returned', [
                'siswa_id' => $siswa->id,
                'tagihan'  => $tagihan->count(),
                'lunas'    => $lunas->count(),
                'ms'       => $ms,
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'siswa_id' => $siswa->id,
                    'siswa'    => [
                        'id'    => $siswa->id,
                        'nama'  => $siswa->name,
                        'nisn'  => $siswa->nisn,
                        'kelas' => $namaKelas,
                    ],
                    'tagihan' => $tagihan->toArray(),
                    'lunas'   => $lunas->toArray(),
                    'summary' => [
                        'jumlah_tagihan'           => $tagihan->count(),
                        'total_tagihan_belum_bayar' => $totalBelumBayar,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            $ms = (int) ((microtime(true) - $startTime) * 1000);
            $this->logRequest($phone, $nisn, null, 'error', $ms, $e->getMessage());

            Log::channel('whatsapp')->error('❌ Check payment error', [
                'error' => $e->getMessage(),
                'nisn'  => $nisn,
            ]);

            return response()->json([
                'success' => false,
                'code'    => 'SERVER_ERROR',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function formatTagihanItem(Charge $charge): array
    {
        $nominal = 'Rp ' . number_format($charge->gross_amount, 0, ',', '.');

        $statusLabel = match ($charge->transaction_status) {
            'pending' => '⏳ Menunggu Pembayaran',
            'expired' => '❌ Kadaluarsa',
            default   => $charge->transaction_status,
        };

        return [
            'judul'     => $charge->name ?? ($charge->kategori_pembayaran->name ?? 'Tagihan'),
            'nominal'   => $nominal,
            'status'    => $statusLabel,
            'bank'      => $charge->bank ?? '-',
            'va_number' => $charge->va_number ?? '-',
            'tanggal'   => $charge->transaction_time
                ? \Carbon\Carbon::parse($charge->transaction_time)->isoFormat('D MMM Y')
                : '-',
        ];
    }

    private function formatLunasItem(Charge $charge): array
    {
        $nominal = 'Rp ' . number_format($charge->gross_amount, 0, ',', '.');

        $tgl = $charge->transaction_time
            ? \Carbon\Carbon::parse($charge->transaction_time)->isoFormat('D MMM Y')
            : ($charge->updated_at ? $charge->updated_at->isoFormat('D MMM Y') : '-');

        $metode = $charge->transaction_status === 'pay_offline' ? '💵 Tunai' : '💳 Online';

        return [
            'judul'   => $charge->name ?? ($charge->kategori_pembayaran->name ?? 'Pembayaran'),
            'nominal' => $nominal,
            'tanggal' => $tgl,
            'metode'  => $metode,
        ];
    }

    private function phoneToLocal(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (str_starts_with($cleaned, '62')) {
            return '0' . substr($cleaned, 2);
        }

        if (str_starts_with($cleaned, '0')) {
            return $cleaned;
        }

        return '0' . $cleaned;
    }

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }
        return '****';
    }

    private function logRequest(
        string  $phone,
        string  $nisn,
        ?string $siswaId,
        string  $status,
        int     $ms,
        ?string $errorMessage = null
    ): void {
        try {
            WaRequestLog::create([
                'phone'            => $phone,
                'nisn'             => $nisn,
                'siswa_id'         => $siswaId,
                'status'           => $status,
                'ip_address'       => request()->ip(),
                'response_time_ms' => $ms,
                'error_message'    => $errorMessage,
                'requested_at'     => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Log request error', ['error' => $e->getMessage()]);
        }
    }
}