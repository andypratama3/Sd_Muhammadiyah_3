<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Pelajaran;
use Illuminate\Support\Str;
use App\Models\JadwalDetail;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data master (PAKAI FIRST)
        $kelas = Kelas::where('name', 'Kelas 1')->first();


        $pelajaran = Pelajaran::first();
        $guru = Guru::first();

        // Guard: pastikan data master ada
        if (!$kelas || !$pelajaran || !$guru) {
            $this->command->warn('Seeder dibatalkan: Kelas / Pelajaran / Guru belum ada.');
            return;
        }

        // ===============================
        // JADWAL (TAHUN AJARAN)
        // ===============================
        $jadwal = Jadwal::firstOrCreate(
            [
                'id' => Str::uuid(),
                'tahun_ajaran' => '2025/2026',
                'kelas_id' => $kelas->id,
            ],
            [
                'category_kelas' => 'BAGHDAD',
                'jadwal' => 'Jadwal Tahun Ajaran 2025/2026',
            ]
        );

        // ===============================
        // DETAIL JADWAL
        // ===============================
        $details = [
            [
                'hari' => 'Senin',
                'time_start' => '07:00',
                'time_end' => '08:30',
                'color' => 'bg-blue-100',
            ],
            [
                'hari' => 'Selasa',
                'time_start' => '08:30',
                'time_end' => '10:00',
                'color' => 'bg-green-100',
            ],
            [
                'hari' => 'Rabu',
                'time_start' => '10:00',
                'time_end' => '11:30',
                'color' => 'bg-orange-100',
            ],
        ];

        foreach ($details as $detail) {
            JadwalDetail::firstOrCreate(
                [
                    'id' => Str::uuid(),
                    'jadwal_id' => $jadwal->id,
                    'hari' => $detail['hari'],
                    'time_start' => $detail['time_start'],
                ],
                [
                    'time_end' => $detail['time_end'],
                    'pelajaran_id' => $pelajaran->id,
                    'guru_id' => $guru->id,
                    'color' => $detail['color'],
                ]
            );
        }

        $this->command->info('Seeder Jadwal & JadwalDetail berhasil dibuat.');
    }
}
