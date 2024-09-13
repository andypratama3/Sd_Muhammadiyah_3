<?php

namespace Database\Factories;

use App\Models\Siswa;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'nisn' => str_pad($this->faker->randomNumber(8), 10, '0', STR_PAD_LEFT),
            'jk' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'tmpt_lahir' => $this->faker->city,
            'tgl_lahir' => $this->faker->date(),
            'spp' => $this->faker->randomNumber(4),
            'kelas_tahun' => $this->faker->year(),
            'tanggal_masuk' => $this->faker->date(),
            // 'nik' => str_pad($this->faker->randomNumber(8), 9, '0', STR_PAD_LEFT),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Hindu', 'Buddha']),
            'rt' => $this->faker->randomNumber(3),
            'rw' => $this->faker->randomNumber(3),
            'provinsi_id' => $this->faker->numberBetween(1, 34),
            'kabupaten_id' => $this->faker->numberBetween(1, 500),
            'kecamatan_id' => $this->faker->numberBetween(1, 1000),
            'kelurahan_id' => $this->faker->numberBetween(1, 5000),
            'nama_jalan' => $this->faker->streetName,
            'jenis_tinggal' => $this->faker->randomElement(['Rumah sendiri', 'Apartemen']),
            'no_hp' => $this->faker->phoneNumber,
            'beasiswa' => $this->faker->randomElement(['Yes', 'No']),
            'foto' => 'siswa' . $this->faker->numberBetween(1, 10) . '.jpg',
            'slug' => Str::slug($this->faker->name),
        ];
    }
}
