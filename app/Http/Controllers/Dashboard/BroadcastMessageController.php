<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendBroadcastWhatsappJob;

class BroadcastMessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|string|exists:kelas,id',
            'isi' => 'required|string|min:5|max:4000',
        ]);

        try {
            // ✅ STEP 1: VALIDASI INPUT
            $kelasId = $request->input('kelas_id');
            $message = $request->input('isi');

            Log::info('Broadcast message store initiated', [
                'kelas_id' => $kelasId,
                'message_length' => strlen($message),
                'user_id' => auth()->id(),
            ]);

            // ✅ STEP 2: GET KELAS REFERENCE (untuk filter)
            $kelasLulus = Kelas::where('name', 'Lulus')->first();
            if (!$kelasLulus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas "Lulus" tidak ditemukan di database',
                ], 400);
            }

            // ✅ STEP 3: BUILD QUERY UNTUK GET SISWA
            $siswaQuery = Siswa::with('kelas')
                // Exclude kelas "Lulus" (sudah lulus)
                ->whereDoesntHave('kelas', function ($query) use ($kelasLulus) {
                    $query->where('kelas.id', $kelasLulus->id);
                });

            // ✅ STEP 4: FILTER BY KELAS JIKA DIBERIKAN
            if (!empty($kelasId)) {
                $siswaQuery->whereHas('kelas', function ($query) use ($kelasId) {
                    $query->where('kelas.id', $kelasId);
                });
            }

            // ✅ STEP 5: GET SISWA (with pagination untuk safety)
            $totalSiswa = $siswaQuery->count();

            if ($totalSiswa === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa yang sesuai kriteria',
                ], 404);
            }

            // ✅ STEP 6: DISPATCH JOB PER SISWA (dengan rate limit)
            $successCount = 0;
            $failureCount = 0;
            $skippedCount = 0;

            // Batching untuk performa
            $siswaQuery->cursor()->each(function ($siswa, $index) use (
                &$successCount,
                &$failureCount,
                &$skippedCount,
                $message,
                $kelasId
            ) {
                try {
                    // ✅ STEP 6A: VALIDASI NOMOR HP
                    if (empty($siswa->no_hp)) {
                        Log::warning('Siswa tanpa nomor HP, skip', [
                            'siswa_id' => $siswa->id,
                            'siswa_name' => $siswa->name,
                        ]);
                        $skippedCount++;
                        return; // continue ke siswa berikutnya
                    }

                    // ✅ STEP 6B: VALIDASI CONSENT
                    $noHp = '62' . ltrim($siswa->no_hp, '0');
                    $consent = DB::table('whatsapp_consents')
                        ->where('phone', $noHp)
                        ->first();

                    if ($consent && !$consent->opted_in) {
                        Log::info('Nomor opted out, skip', [
                            'siswa_id' => $siswa->id,
                            'phone' => substr($noHp, 0, 2) . '***' . substr($noHp, -4),
                        ]);
                        $skippedCount++;
                        return;
                    }

                    // ✅ STEP 6C: DISPATCH JOB DENGAN DELAY
                    // Delay: 10 + (index * 2) detik untuk prevent rate limit
                    $delay = 10 + ($index * 2);

                    SendBroadcastWhatsappJob::dispatch(
                        $siswa->id,
                        $message,
                        $kelasId
                    )
                    ->onQueue('whatsapp')
                    ->delay(now()->addSeconds($delay));

                    Log::info('Job dispatched for siswa', [
                        'siswa_id' => $siswa->id,
                        'siswa_name' => $siswa->name,
                        'delay_seconds' => $delay,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    Log::error('Error dispatching job for siswa', [
                        'siswa_id' => $siswa->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failureCount++;
                }
            });

            // ✅ STEP 7: RETURN RESPONSE
            Log::info('Broadcast message completed', [
                'total_siswa' => $totalSiswa,
                'jobs_dispatched' => $successCount,
                'jobs_failed' => $failureCount,
                'skipped' => $skippedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Broadcast message queued successfully',
                'data' => [
                    'total_siswa' => $totalSiswa,
                    'jobs_dispatched' => $successCount,
                    'jobs_failed' => $failureCount,
                    'skipped' => $skippedCount,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Broadcast store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing broadcast message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show broadcast status / history
     * GET /broadcast/status/{siswa_id}
     */
    public function status($siswaId)
    {
        try {
            $logs = DB::table('whatsapp_message_logs')
                ->where('siswa_id', $siswaId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
