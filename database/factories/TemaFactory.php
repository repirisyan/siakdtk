<?php

namespace Database\Factories;

use App\Models\Tema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tema>
 */
class TemaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'nama_tema' => $this->faker->randomElement([

                'Diriku',

                'Keluargaku',

                'Lingkunganku',

                'Binatang',

                'Tanaman',

                'Kendaraan',

                'Alam Semesta',

                'Negaraku',

                'Pekerjaan',

                'Rekreasi',

            ]),

        ];
    }
}
