<?php

namespace Database\Factories;

use App\Models\LokasiAbsensi;
use Illuminate\Database\Eloquent\Factories\Factory;

class LokasiAbsensiFactory extends Factory
{
    protected $model = LokasiAbsensi::class;

    public function definition(): array
    {
        return [
            'nama_lokasi' => 'SD Muhammadiyah 3 Samarinda',
            'latitude'    => -0.9020,
            'longitude'   => 116.8529,
            'radius'      => 500,
            'status'      => 'aktif',
        ];
    }
}