<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Jobs\ChargeDppJob;
use Illuminate\Console\Command;
use App\Models\Charge;

class ChargeDppCommand extends Command
{
    protected $signature = 'app:charge-dpp-command';
    protected $description = 'Kirim job pengisian DPP ke queue';

    public function handle()
    {
        $this->info("Mulai kirim job DPP...");

        $siswas = Siswa::all();

        foreach ($siswas as $index => $siswa) {
            $existingCharges = Charge::where('siswa_id', $siswa->id)
                ->whereHas('kategori_pembayaran', function ($q) {
                    $q->where('name', 'DPP');
                })->count();

            if ($existingCharges >= 2) {
                $this->warn("Lewati {$siswa->name}, sudah ada 2 tagihan DPP.");
                continue;
            }

            try {
                $charge = ChargeDppJob::dispatch($siswa)->delay(now()->addSeconds($index * 2));

                $this->info("Job DPP untuk {$siswa->name} dikirim.");
            } catch (\Exception $e) {
                \Log::error("Gagal dispatch job untuk {$siswa->name}: " . $e->getMessage());
                $this->error("❌ Gagal dispatch job untuk {$siswa->name}: " . $e->getMessage());
            }
        }

        $this->info("Semua job DPP selesai dikirim ke queue.");
    }
}
