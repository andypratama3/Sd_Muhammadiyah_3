<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Siswa;
use App\Models\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembayaranDataController extends Controller
{
    /**
     * Search student by NISN
     *
     * GET /api/pembayaran/search
     * Query params:
     * - nisn: required string
     */
    public function search(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|min:10|max:10',
        ]);

        try {
            // Find student by NISN
            $siswa = Siswa::where('nisn', $request->nisn)->first();

            // Return 404 if student not found
            if (!$siswa) {
                return $this->notFound('NISN tidak ditemukan');
            }

            // Get all charges for student
            $charges = Charge::with('kategori_pembayaran')
                ->where('siswa_id', $siswa->id)
                ->whereNull('deleted_at')
                ->get()
                ->map(function ($charge) {
                    return [
                        'id' => $charge->id,
                        'name' => $charge->name,
                        'category_id' => $charge->category_payment_id,
                        'category' => $charge->kategori_pembayaran?->name ?? 'Lainnya',
                        'amount' => (int) $charge->gross_amount,
                        'status' => $this->mapTransactionStatus($charge->transaction_status),
                        'transaction_id' => $charge->transaction_id,
                        'transaction_time' => $charge->transaction_time,
                        'va_number' => $charge->va_number,
                        'snap_token' => $charge->snap_token,
                        'created_at' => $charge->created_at,
                        'updated_at' => $charge->updated_at,
                    ];
                })
                ->groupBy('category') // Group by category
                ->toArray();

            // Return success response
            return $this->success([
                'siswa' => [
                    'id' => $siswa->id,
                    'name' => $siswa->name,
                    'nisn' => $siswa->nisn,
                    'foto' => $siswa->foto,
                    'no_hp' => $siswa->no_hp,
                    'kelas_tahun' => $siswa->kelas->pluck('name')->implode(', '),
                    'nama_ayah' => $siswa->nama_ayah,
                    'nama_ibu' => $siswa->nama_ibu,
                    'jk' => $siswa->jk,
                    'tgl_lahir' => $siswa->tgl_lahir,
                ],
                'charges' => $charges,
            ], 'Data siswa dan pembayaran berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->success('NISN tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil data pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Get payment statistics by student
     *
     * GET /api/pembayaran/{siswa_id}/statistics
     */
    public function statistics($siswa_id)
    {
        try {
            $siswa = Siswa::findOrFail($siswa_id);

            $charges = Charge::where('siswa_id', $siswa_id)
                ->whereNull('deleted_at')
                ->get();

            $totalAmount = $charges->sum('gross_amount');
            $paidAmount = $charges->where('transaction_status', 'settlement')
                ->sum('gross_amount');
            $unpaidAmount = $totalAmount - $paidAmount;
            $pendingAmount = $charges->where('transaction_status', 'pending')
                ->sum('gross_amount');

            return $this->success([
                'total_amount' => (int) $totalAmount,
                'paid_amount' => (int) $paidAmount,
                'unpaid_amount' => (int) $unpaidAmount,
                'pending_amount' => (int) $pendingAmount,
                'paid_count' => $charges->where('transaction_status', 'settlement')->count(),
                'unpaid_count' => $charges->where('transaction_status', '!=', 'settlement')->count(),
                'total_count' => $charges->count(),
            ], 'Statistik pembayaran berhasil diambil');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Siswa tidak ditemukan');
        } catch (\Exception $e) {
            return $this->serverError('Gagal mengambil statistik: ' . $e->getMessage());
        }
    }

    /**
     * Map transaction status to frontend format
     */
    private function mapTransactionStatus($status)
    {
        return match($status) {
            'settlement' => 'paid',
            'pay_offline' => 'paid',
            'pending' => 'unpaid',
            'expired' => 'unpaid',
            'failed' => 'unpaid',
            default => 'unpaid',
        };
    }
}
