<?php

namespace Database\Factories;

use App\Models\JenisPembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JenisPembayaran> */
class JenisPembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_jenis' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'status' => true,
        ];
    }
}
