<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('jam_kerja')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $hariKerja = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $hariLibur = ['sabtu', 'minggu'];

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA GURU (SENIN - JUMAT)
        |--------------------------------------------------------------------------
        | - jam_masuk: Jam masuk ideal
        | - batas_masuk: Batas maksimal masuk (lewat ini = terlambat)
        | - jam_pulang: Jam pulang normal
        | - batas_pulang: Jam minimal boleh pulang (sebelum ini = pulang cepat)
        |--------------------------------------------------------------------------
        */
        foreach ($hariKerja as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => $hari,
                'jam_masuk'     => '07:00:00',
                'batas_masuk'   => '07:15:00',  // Lewat jam ini = terlambat
                'jam_pulang'    => '14:00:00',
                'batas_pulang'  => '14:00:00',  // Boleh pulang mulai jam ini
                'is_default'    => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | HARI LIBUR GURU (SABTU - MINGGU)
        |--------------------------------------------------------------------------
        */
        foreach ($hariLibur as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Libur Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => $hari,
                'jam_masuk'     => null,
                'batas_masuk'   => null,
                'jam_pulang'    => null,
                'batas_pulang'  => null,
                'is_default'    => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA TENAGA PENDIDIKAN (SENIN - JUMAT)
        |--------------------------------------------------------------------------
        */
        foreach ($hariKerja as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Tenaga Pendidikan',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => $hari,
                'jam_masuk'     => '07:30:00',
                'batas_masuk'   => '07:45:00',
                'jam_pulang'    => '15:30:00',
                'batas_pulang'  => '15:30:00',
                'is_default'    => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | HARI LIBUR TENAGA PENDIDIKAN (SABTU - MINGGU)
        |--------------------------------------------------------------------------
        */
        foreach ($hariLibur as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Libur Tendik',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => $hari,
                'jam_masuk'     => null,
                'batas_masuk'   => null,
                'jam_pulang'    => null,
                'batas_pulang'  => null,
                'is_default'    => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA DEFAULT (FALLBACK) - Hanya untuk hari tidak terdefinisi
        |--------------------------------------------------------------------------
        */
        DB::table('jam_kerja')->insert([
            // DEFAULT GURU
            [
                'nama_shift'    => 'Default Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => null,
                'jam_masuk'     => '07:00:00',
                'batas_masuk'   => '07:15:00',
                'jam_pulang'    => '14:00:00',
                'batas_pulang'  => '14:00:00',
                'is_default'    => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            // DEFAULT TENAGA PENDIDIKAN
            [
                'nama_shift'    => 'Default Tendik',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => null,
                'jam_masuk'     => '07:30:00',
                'batas_masuk'   => '07:45:00',
                'jam_pulang'    => '15:30:00',
                'batas_pulang'  => '15:30:00',
                'is_default'    => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        $this->command->info('✅ Seeder Jam Kerja berhasil! (Senin-Jumat: kerja, Sabtu-Minggu: libur)');
    }
}
