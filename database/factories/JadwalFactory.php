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
        $tema = Tema::inRandomOrder()->first()

            ?? Tema::factory()->create();

        $subTema = SubTema::where('tema_id', $tema->id)

            ->inRandomOrder()

            ->first();

        if (! $subTema) {

            $subTema = SubTema::factory()->create([

                'tema_id' => $tema->id,

            ]);

        }

        return [

            'kelas_id' => Kelas::inRandomOrder()->value('id')

                ?? Kelas::factory()->create()->id,

            'guru_id' => Guru::inRandomOrder()->value('id')

                ?? Guru::factory()->create()->id,

            'tema_id' => $tema->id,

            'sub_tema_id' => $subTema->id,

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
}
