<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\RaporAkhir;
use App\Models\RaporAkhirDetail;
use App\Models\Tema;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RaporAkhirDetail> */
class RaporAkhirDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rapor_akhir_id' => RaporAkhir::factory(),
            'tema_id' => Tema::factory(),
            'guru_id' => Guru::factory(),
            'keterangan' => fake()->paragraph(),
        ];
    }
}
