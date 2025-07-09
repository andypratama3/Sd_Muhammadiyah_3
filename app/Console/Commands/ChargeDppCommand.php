<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use App\Jobs\ChargeDppJob;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChargeDppCommand extends Command
{

    protected $signature = 'app:charge-dpp-command';
    protected $whatsapp;

    protected $description = 'Command description';

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
                $charge = ChargeDppJob::dispatch($siswa)->delay(now()->addSeconds($index * 2));
                } catch (\Throwable $e) {
                    \Log::error("Gagal ChargeDppJob untuk siswa {$siswa->name}: " . $e->getMessage());
                }
        }

        $this->info("Semua job pengisian DPP telah dikirim ke queue.");
    }

}
