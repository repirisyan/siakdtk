<?php

namespace Database\Factories;

use App\Models\SchoolSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolSetting> */
class SchoolSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_sekolah' => fake()->company().' Kindergarten',
            'tagline' => fake()->sentence(),
            'alamat' => fake()->address(),
            'desa' => fake()->citySuffix().' '.fake()->word(),
            'kecamatan' => fake()->citySuffix().' '.fake()->word(),
            'kabupaten' => fake()->city(),
            'provinsi' => fake()->state(),
            'nomor_telepon' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'pendaftaran_dibuka' => true,
        ];
    }
}
