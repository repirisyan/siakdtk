<?php

namespace Database\Factories;

use App\Models\KomponenPenilaian;
use App\Models\SubTema;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KomponenPenilaian> */
class KomponenPenilaianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sub_tema_id' => SubTema::factory(),
            'nama_komponen' => fake()->unique()->words(3, true),
            'deskripsi' => fake()->sentence(),
            'status' => true,
        ];
    }
}
