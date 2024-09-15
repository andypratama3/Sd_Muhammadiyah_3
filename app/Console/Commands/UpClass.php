<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:up-class';

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
        $siswas = Siswa::all();

        // Retrieve the "Lulus" class if it exists
        $lulus = Kelas::where('name', 'Lulus')->firstOrFail();

        // Loop through each student
        foreach ($siswas as $siswa) {
            // Get the current class of the student
            $currentKelas = $siswa->kelas()->first();

            // If the current class is "Lulus" or greater than 6, move the student to "Lulus" class and continue to the next student
            if ($currentKelas && intval(substr($currentKelas->name, 6)) >= 6) {
                $siswa->kelas()->sync([$lulus->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
                continue; // Skip the rest of the loop for this student
            }

            // If current class is less than 6, increment the class by 1
            if ($currentKelas) {
                preg_match('/Kelas (\d+)/', $currentKelas->name, $matches);
                if (isset($matches[1])) {
                    $nextClassNumber = intval($matches[1]) + 1;
                    $nextClassName = "Kelas $nextClassNumber";
                    $nextClass = Kelas::where('name', $nextClassName)->first();
                    if ($nextClass) {
                        $siswa->kelas()->sync([$nextClass->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
                        // $siswa->pembayarans()->sync([$nextClass->id => ['category_kelas' => $siswa->pembayarans->first()->pivot->category_kelas]]);
                    }
                }
            }
        }

        // Optionally, you may return a response indicating the process is completed
        $this->info('Fungsi Naik Kelas Berhasil');
    }
}
