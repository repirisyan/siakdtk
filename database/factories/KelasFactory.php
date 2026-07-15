<?php

namespace Database\Factories;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'nama_kelas' => 'TK '.$this->faker->unique()->bothify('??-##'),

            'thn_ajaran' => $this->faker->numberBetween(

                2025,

                2030

            ),

            'status' => true,

        ];
    }
}
