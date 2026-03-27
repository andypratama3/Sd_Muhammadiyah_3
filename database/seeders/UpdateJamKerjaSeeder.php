<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateJamKerjaSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // JAM KERJA (SENIN - JUMAT): Guru, Tendik, Shadow Teacher
        // jam_masuk: 06:45 | batas_masuk: 07:00 | jam_pulang: 14:15 | batas_pulang: 14:15
        // ============================================
        $hariKerjaIds = [
            // Guru: Senin - Jumat
            1, 2, 3, 4, 5,
            // Tendik: Senin - Jumat
            8, 9, 10, 11, 12,
            // Shadow Teacher: Senin - Jumat
            15, 16, 17, 18, 19,
        ];

        DB::table('jam_kerja')
            ->whereIn('id', $hariKerjaIds)
            ->update([
                'jam_masuk'    => '06:45:00',
                'batas_masuk'  => '07:00:00',
                'jam_pulang'   => '14:15:00',
                'batas_pulang' => '14:15:00',
                'updated_at'   => now(),
            ]);

        // ============================================
        // HARI LIBUR (SABTU - MINGGU): semua jenis pegawai
        // Guru: id 6, 7 | Tendik: id 13, 14 | Shadow Teacher: id 20, 21
        // ============================================
        $hariLiburIds = [6, 7, 13, 14, 20, 21];

        DB::table('jam_kerja')
            ->whereIn('id', $hariLiburIds)
            ->update([
                'jam_masuk'    => '00:00:00',
                'batas_masuk'  => '00:00:00',
                'jam_pulang'   => '00:00:00',
                'batas_pulang' => '00:00:00',
                'updated_at'   => now(),
            ]);

        // ============================================
        // DEFAULT (FALLBACK): Guru, Tendik, Shadow Teacher
        // id 22, 23, 24
        // ============================================
        $defaultIds = [22, 23, 24];

        DB::table('jam_kerja')
            ->whereIn('id', $defaultIds)
            ->update([
                'jam_masuk'    => '06:45:00',
                'batas_masuk'  => '07:15:00',
                'jam_pulang'   => '13:15:00',
                'batas_pulang' => '13:15:00',
                'updated_at'   => now(),
            ]);

        $this->command->info('✅ Update Jam Kerja berhasil! (Guru, Tendik, Shadow Teacher)');
    }
}