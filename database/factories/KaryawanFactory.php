<?php

namespace Database\Factories;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        return [
            'name'          => $name,
            'sex'           => $this->faker->randomElement(['Laki-Laki', 'Perempuan']),
            'phone'         => $this->faker->phoneNumber(),
            'nip'           => $this->faker->unique()->numerify('##############'),
            'user_id'       => null,
            'jenis_pegawai' => 'guru',
        ];
    }
}