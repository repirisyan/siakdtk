<?php

namespace Database\Factories;

use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Absen> */
class AbsenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'siswa_id' => Siswa::factory(),
            'jadwal_id' => Jadwal::factory(),
            'status' => fake()->randomElement(['hadir', 'izin', 'sakit', 'alfa']),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Absen $absen): void {
            $absen->siswa()->update(['kelas_id' => $absen->jadwal()->value('kelas_id')]);
        });
    }
}
