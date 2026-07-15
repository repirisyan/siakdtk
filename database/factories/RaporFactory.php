<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rapor> */
class RaporFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->orangTua(),
            'siswa_id' => Siswa::factory(),
            'guru_id' => Guru::factory(),
            'tema_id' => Tema::factory(),
            'sub_tema_id' => SubTema::factory(),
            'keterangan' => fake()->paragraph(),
            'thn_ajaran' => now()->year,
            'status' => 'draft',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Rapor $rapor): void {
            $rapor->update([
                'user_id' => $rapor->siswa()->value('user_id'),
                'tema_id' => $rapor->subTema()->value('tema_id'),
            ]);
        });
    }
}
