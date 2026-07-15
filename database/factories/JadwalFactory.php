<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SubTema;
use App\Models\Tema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jadwal>
 */
class JadwalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Jadwal::class;

    public function definition(): array
    {
        return [
            'kelas_id' => Kelas::factory(),

            'guru_id' => Guru::factory(),

            'tema_id' => Tema::factory(),

            'sub_tema_id' => SubTema::factory(),

            'tanggal' => $this->faker->date(),

            'jam_mulai' => $this->faker->randomElement([

                '07:00:00',

                '08:00:00',

                '09:00:00',

                '10:00:00',

            ]),

            'jam_selesai' => $this->faker->randomElement([

                '10:00:00',

                '11:00:00',

                '12:00:00',

            ]),

        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Jadwal $jadwal): void {
            $jadwal->update(['tema_id' => $jadwal->subTema()->value('tema_id')]);
        });
    }
}
