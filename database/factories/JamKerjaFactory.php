<?php

namespace Database\Factories;

use App\Models\JamKerja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class JamKerjaFactory extends Factory
{
    protected $model = JamKerja::class;

    public function definition(): array
    {
        return [
            'jenis_pegawai' => 'guru',
            'hari'          => strtolower(Carbon::now('Asia/Makassar')->locale('id')->dayName),
            'jam_masuk'     => '06:45:00',
            'batas_masuk'   => '07:00:00',
            'jam_pulang'    => '14:00:00',
            'batas_pulang'  => '14:00:00',
            'is_default'    => true,
        ];
    }
}