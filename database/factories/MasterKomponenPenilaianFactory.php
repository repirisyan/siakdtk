<?php

namespace Database\Factories;

use App\Models\MasterKomponenPenilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MasterKomponenPenilaian> */
class MasterKomponenPenilaianFactory extends Factory
{
    protected $model = MasterKomponenPenilaian::class;

    public function definition(): array
    {
        return [
            'nama_komponen' => fake()->unique()->words(3, true),
            'deskripsi' => fake()->sentence(),
            'status' => true,
        ];
    }
}
