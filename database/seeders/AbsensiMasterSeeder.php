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
        */
        foreach ($hariKerja as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => $hari,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:00:00',  // Lewat jam ini = terlambat
                'jam_pulang'    => '14:15:00',
                'batas_pulang'  => '14:15:00',  // Boleh pulang mulai jam ini
                'is_default'    => false,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | HARI LIBUR GURU
        |--------------------------------------------------------------------------
        */
        foreach ($hariLibur as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Libur Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => $hari,
                'jam_masuk'     => '00:00:00',
                'batas_masuk'   => '00:00:00',
                'jam_pulang'    => '00:00:00',
                'batas_pulang'  => '00:00:00',
                'is_default'    => false,
                'is_hari_kerja' => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA TENAGA PENDIDIKAN
        |--------------------------------------------------------------------------
        */
        foreach ($hariKerja as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Tenaga Pendidikan',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => $hari,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:00:00',  // Lewat jam ini = terlambat
                'jam_pulang'    => '14:15:00',
                'batas_pulang'  => '14:15:00',
                'is_default'    => false,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | HARI LIBUR TENAGA PENDIDIKAN
        |--------------------------------------------------------------------------
        */
        foreach ($hariLibur as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Libur Tendik',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => $hari,
                'jam_masuk'     => '00:00:00',
                'batas_masuk'   => '00:00:00',
                'jam_pulang'    => '00:00:00',
                'batas_pulang'  => '00:00:00',
                'is_default'    => false,
                'is_hari_kerja' => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA SHADOW TEACHER (SENIN - JUMAT)
        |--------------------------------------------------------------------------
        */
        foreach ($hariKerja as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Shadow Teacher',
                'jenis_pegawai' => 'shadow-teacher',
                'hari'          => $hari,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:00:00',  // Lewat jam ini = terlambat
                'jam_pulang'    => '14:15:00',
                'batas_pulang'  => '14:15:00',
                'is_default'    => false,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | HARI LIBUR SHADOW TEACHER
        |--------------------------------------------------------------------------
        */
        foreach ($hariLibur as $hari) {
            DB::table('jam_kerja')->insert([
                'nama_shift'    => 'Libur Shadow Teacher',
                'jenis_pegawai' => 'shadow-teacher',
                'hari'          => $hari,
                'jam_masuk'     => '00:00:00',
                'batas_masuk'   => '00:00:00',
                'jam_pulang'    => '00:00:00',
                'batas_pulang'  => '00:00:00',
                'is_default'    => false,
                'is_hari_kerja' => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | JAM KERJA DEFAULT (FALLBACK)
        |--------------------------------------------------------------------------
        */
        DB::table('jam_kerja')->insert([
            [
                'nama_shift'    => 'Default Guru',
                'jenis_pegawai' => 'guru',
                'hari'          => null,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:15:00',
                'jam_pulang'    => '13:15:00',
                'batas_pulang'  => '13:15:00',
                'is_default'    => true,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_shift'    => 'Default Tendik',
                'jenis_pegawai' => 'tenaga-pendidikan',
                'hari'          => null,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:15:00',
                'jam_pulang'    => '13:15:00',
                'batas_pulang'  => '13:15:00',
                'is_default'    => true,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_shift'    => 'Default Shadow Teacher',
                'jenis_pegawai' => 'shadow-teacher',
                'hari'          => null,
                'jam_masuk'     => '06:45:00',
                'batas_masuk'   => '07:15:00',
                'jam_pulang'    => '13:15:00',
                'batas_pulang'  => '13:15:00',
                'is_default'    => true,
                'is_hari_kerja' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        $this->command->info('✅ Seeder Jam Kerja berhasil! (Guru, Tendik, Shadow Teacher)');
    }
}
