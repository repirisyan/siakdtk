<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RaporAkhir> */
class RaporAkhirFactory extends Factory
{
    public function definition(): array
    {
        return [
            'siswa_id' => Siswa::factory(),
            'kelas_id' => Kelas::factory(),
            'thn_ajaran' => now()->year,
            'status' => 'draft',
        ];
    }
}
