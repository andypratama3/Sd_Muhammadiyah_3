<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Charge;
use App\Jobs\ChargePaymentJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChargePayment extends Command
{
    protected $signature = 'app:charge-payment {--force : Bypass safety checks}';
    protected $description = 'Charge monthly payments for all active students';

    public function handle()
    {
        $this->showEnvironmentInfo();

        // ✅ Prevent duplicate runs
        if (!$this->option('force') && $this->isAlreadyRunning()) {
            $this->warn('⚠️  Command is already running. Use --force to override.');
            return 1;
        }

        try {
            $this->setRunningFlag();

            // Get "Lulus" class
            $kelasLulus = Kelas::where('name', 'Lulus')->first();
            if (!$kelasLulus) {
                $this->error('❌ Kelas "Lulus" tidak ditemukan.');
                return 1;
            }

            // ✅ Check if already charged today
            $today = now()->format('Y-m-d');
            if ($this->hasRunToday() && !$this->option('force')) {
                $this->warn("⚠️  Already charged today ({$today}). Use --force to run again.");
                return 1;
            }

            $this->info("Processing charge payment for {$today}...\n");

            // Get active students (not graduated)
            $siswaQuery = Siswa::whereDoesntHave('kelas', function ($query) use ($kelasLulus) {
                $query->where('kelas.id', $kelasLulus->id);
            })
            ->cursor();

            $totalSiswa = 0;
            $jobDispatched = 0;
            $jobFailed = 0;

            // ✅ Batch dispatch untuk performa
            $batch = [];
            $batchSize = 100;

            foreach ($siswaQuery as $siswa) {
                try {
                    // ✅ Check if already charged this month
                    if ($this->isAlreadyChargedThisMonth($siswa)) {
                        $this->comment("⊘ {$siswa->name} - Sudah di-charge bulan ini");
                        continue;
                    }

                    // ✅ Dispatch job
                    ChargePaymentJob::dispatch($siswa);
                    $batch[] = $siswa->id;
                    $jobDispatched++;

                    // Show progress
                    $this->line("✅ {$siswa->name} ({$siswa->nisn})");

                    // Batch dispatch setiap 100 items
                    if (count($batch) >= $batchSize) {
                        $this->info("  → {$jobDispatched} jobs dispatched...");
                        $batch = [];
                    }

                } catch (\Throwable $e) {
                    $jobFailed++;
                    $this->error("❌ {$siswa->name}: " . $e->getMessage());
                    Log::error('ChargePayment command failed', [
                        'siswa_id' => $siswa->id,
                        'siswa_name' => $siswa->name,
                        'error' => $e->getMessage(),
                    ]);
                }

                $totalSiswa++;
            }

            // ✅ Summary
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info("Total siswa diproses: {$totalSiswa}");
            $this->info("Jobs di-dispatch: {$jobDispatched}");
            if ($jobFailed > 0) {
                $this->error("Jobs gagal: {$jobFailed}");
            }
            $this->info('═══════════════════════════════════════');

            if ($jobDispatched === 0) {
                $this->warn('⚠️  Tidak ada siswa yang di-charge.');
                return 1;
            }

            // ✅ Log success
            Log::info('ChargePayment command completed successfully', [
                'total_siswa' => $totalSiswa,
                'jobs_dispatched' => $jobDispatched,
                'jobs_failed' => $jobFailed,
                'date' => $today,
            ]);

            $this->info('✅ Semua job berhasil dikirim ke queue!');
            return 0;

        } catch (\Exception $e) {
            Log::critical('ChargePayment command failed critically', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("❌ Error: " . $e->getMessage());
            return 1;

        } finally {
            $this->clearRunningFlag();
        }
    }

    /**
     * Check if command is already running
     */
    private function isAlreadyRunning(): bool
    {
        return Cache::has('charge-payment-running');
    }

    /**
     * Set running flag
     */
    private function setRunningFlag(): void
    {
        Cache::put('charge-payment-running', true, now()->addMinutes(30));
    }

    /**
     * Clear running flag
     */
    private function clearRunningFlag(): void
    {
        Cache::forget('charge-payment-running');
    }

    /**
     * Check if already charged today
     */
    private function hasRunToday(): bool
    {
        return Cache::has('charge-payment-today-' . now()->format('Y-m-d'));
    }

    /**
     * Mark as charged today
     */
    private function setChargedToday(): void
    {
        Cache::put(
            'charge-payment-today-' . now()->format('Y-m-d'),
            true,
            now()->endOfDay()
        );
    }

    /**
     * Check if student already charged this month
     */
    private function isAlreadyChargedThisMonth(Siswa $siswa): bool
    {
        $currentMonth = now()->format('Y-m');

        return Charge::where('siswa_id', $siswa->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();
    }

    /**
     * Show environment information
     */
    private function showEnvironmentInfo(): void
    {
        $env = app()->environment();
        $icon = $env === 'production' ? '🔴' : '🟡';

        $this->newLine();
        $this->info("{$icon} Running in " . strtoupper($env) . " environment");

        if ($env === 'production') {
            $this->comment('   Queue Driver: ' . config('queue.default'));
            $this->comment('   Database: ' . config('database.default'));
        }

        $this->newLine();
    }
}
