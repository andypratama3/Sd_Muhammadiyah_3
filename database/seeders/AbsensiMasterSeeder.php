<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbsensiMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('lokasi_absensi')->truncate();
        DB::table('jam_kerja')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /**
         * ===============================
         * LOKASI ABSENSI
         * ===============================
         */
        DB::table('lokasi_absensi')->insert([
            [
                'id' => 1,
                'nama_lokasi' => 'Sekolah Kreatif SD Muhammadiyah 3 Samarinda',
                'latitude' => -0.5093409028305138,
                'longitude' => 117.12975017880773,
                'radius' => 150,
                'alamat' => 'Kota Samarinda',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * ===============================
         * JAM KERJA - GURU
         * ===============================
         */
        DB::table('jam_kerja')->insert([
            [
                'nama_shift' => 'Shift Pagi Guru',
                'jenis_pegawai' => 'guru',
                'jam_masuk' => '07:00:00',
                'batas_masuk' => '07:15:00',
                'jam_pulang' => '14:00:00',
                'batas_pulang' => '13:45:00',
                'hari' => null,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * ===============================
         * JAM KERJA - TENAGA PENDIDIKAN
         * ===============================
         */ 
        DB::table('jam_kerja')->insert([
            [
                'nama_shift' => 'Shift Pagi Tendik',
                'jenis_pegawai' => 'tenaga_pendidikan',
                'jam_masuk' => '07:30:00',
                'batas_masuk' => '07:45:00',
                'jam_pulang' => '15:30:00',
                'batas_pulang' => '15:00:00',
                'hari' => null,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Seeder Absensi (Lokasi & Jam Kerja) berhasil dijalankan.');
    }
}
