<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Jobs\ChargePaymentJob;
use Illuminate\Console\Command;

class ChargePayment extends Command
{
    protected $signature = 'app:charge-payment';
    protected $description = 'Charge payments for all students';

    public function handle()
    {
        if (app()->environment('production')) {
            $this->info("Running in PRODUCTION environment.");
        } else {
            $this->warn("Running in NON-PRODUCTION environment.");
        }

        $siswas = Siswa::all();

        foreach ($siswas as $index => $siswa) {
            try {
                ChargePaymentJob::dispatch($siswa)->delay(now()->addSeconds($index * 2));
            } catch (\Throwable $e) {
                \Log::error("Gagal ChargePaymentJob untuk siswa {$siswa->name}: " . $e->getMessage());
            }
        }

        $this->info("Semua job pengisian Charge Payment telah dikirim ke queue.");
    }
}
