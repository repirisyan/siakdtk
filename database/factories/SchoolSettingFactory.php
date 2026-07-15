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
            'nomor_telepon' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'pendaftaran_dibuka' => true,
        ];
    }
}
