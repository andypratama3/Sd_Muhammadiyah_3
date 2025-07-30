<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Console\Command;

class SyncDataSiswaAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-data-siswa-attendances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $kelasLulus = Kelas::where('name', 'Lulus')->firstOrFail();
        $siswaQuery = Siswa::whereDoesntHave('kelas', function ($query) use ($kelasLulus) {
            $query->where('kelas.id', $kelasLulus->id);
        })->cursor();


        $index = 0;
        foreach ($siswaQuery as $siswa) {
            try {
                SyncDataSiswaAttendancesJob::dispatch($siswa)->delay(now()->addSeconds($index * 2));
                $index++;
            } catch (\Throwable $e) {
                \Log::error('Job gagal: ' . $e->getMessage());
                throw $e;
            }
        }




    }
}
