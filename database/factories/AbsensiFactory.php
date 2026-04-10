<?php

namespace Database\Factories;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsensiFactory extends Factory
{
    protected $model = Absensi::class;

    public function definition(): array
    {
        return [
            'karyawan_id'      => Karyawan::factory(),
            'lokasi_absensi_id'=> null,
            'jam_kerja_id'     => null,
            'tanggal'          => today()->format('Y-m-d'),
            'status_kehadiran' => 'hadir',
            'jam_masuk'        => '06:45:00',
            'jam_pulang'       => null,
            'status_masuk'     => 'tepat_waktu',
            'status_pulang'    => null,
            'rp_masuk'         => 4000,
            'rp_pulang'        => 0,
            'latitude_masuk'   => null,
            'longitude_masuk'  => null,
            'jarak_masuk'      => null,
            'ip_address'       => null,
            'user_agent'       => null,
            'device_id'        => null,
        ];
    }
}