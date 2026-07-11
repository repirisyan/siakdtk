<?php

namespace Database\Factories;

use App\Models\Konten;
use App\Models\KontenGaleri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KontenGaleri>
 */
class KontenGaleriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'konten_id' => Konten::factory(),

            'gambar' => 'konten/gallery/'.fake()->uuid().'.webp',

            'caption' => fake()->sentence(),

            'urutan' => fake()->numberBetween(1, 20),

        ];
    }
}
