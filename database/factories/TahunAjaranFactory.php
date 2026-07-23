<?php

namespace Database\Factories;

use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahunAjaran>
 */
class TahunAjaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tahun_ajaran' => fake()->unique()->numberBetween(2025, 2035),
        ];
    }
}
