<?php

namespace Database\Factories;

use App\Models\Absen;
use App\Models\KomponenPenilaian;
use App\Models\Nilai;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Nilai> */
class NilaiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'absen_id' => Absen::factory()->state(['status' => 'hadir']),
            'komponen_penilaian_id' => KomponenPenilaian::factory(),
            'nilai' => fake()->randomElement(['A', 'B', 'C']),
            'keterangan' => fake()->sentence(),
            'foto_kegiatan' => null,
        ];
    }
}
