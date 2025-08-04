<?php

namespace App\Console\Commands;

use App\Models\Kelas;
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

        $kelasLulus = Kelas::where('name', 'Lulus')->first();

        if (!$kelasLulus) {
            $this->error("Kelas 'Lulus' tidak ditemukan.");
            return;
        }

        $kelasLulusId = $kelasLulus->id;

        // Ambil siswa yang TIDAK berada di kelas 'Lulus'
        $siswaQuery = Siswa::whereDoesntHave('kelas', function ($query) use ($kelasLulusId) {
            $query->where('kelas.id', $kelasLulusId);
        }) ->where('no_hp', ['085750893938'])
        ->cursor();

        $index = 0;
        foreach ($siswaQuery as $siswa) {
            try {
                ChargePaymentJob::dispatch($siswa)->delay(now()->addSeconds($index * 2));
                $index++;
            } catch (\Throwable $e) {
                \Log::error('Job gagal: ' . $e->getMessage());
                throw $e;
            }
        }

        $this->info("Semua job Charge Payment telah dikirim ke queue.");
    }
}
