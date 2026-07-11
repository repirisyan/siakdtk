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
    protected $model = Tema::class;

    public function definition(): array

    {

        return [

            'nama_tema' => fake()->randomElement([

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

            'thn_ajaran' => fake()->numberBetween(

                now()->year - 1,

                now()->year + 1

            ),

        ];

    }
}
