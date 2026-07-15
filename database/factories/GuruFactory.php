<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guru>
 */
class GuruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->guru(),

            'nama' => $this->faker->name(),

            'nip' => $this->faker->unique()->numerify('####################'),

            'tmp_lhr' => $this->faker->city(),

            'tgl_lahir' => $this->faker->date(

                'Y-m-d',

                '-25 years'

            ),

            'alamat' => $this->faker->address(),

            'agama' => $this->faker->randomElement([

                'Islam',

                'Kristen',

                'Katolik',

                'Hindu',

                'Buddha',

                'Konghucu',

            ]),

            'nohp_guru' => $this->faker->unique()->numerify('08############'),

            'email' => $this->faker->unique()->safeEmail(),

            'jk' => $this->faker->randomElement([

                'L',

                'P',

            ]),

            'jenis_ptk' => $this->faker->randomElement([

                'Guru Kelas',

                'Guru Mapel',

            ]),

            'nuptk' => $this->faker->optional()->numerify('############'),

            'pendidikan' => $this->faker->randomElement([

                'SMA',

                'D3',

                'S1',

                'S2',

            ]),

            'stts_kepegawaian' => $this->faker->randomElement([

                'PNS',

                'PPPK',

                'Honorer',

            ]),

            'sk_cpns' => null,

            'tgl_cpns' => null,

            'sk_pengangkatan' => null,

            'tmt_pengangkatan' => null,

            'pangkat_golongan' => null,

            'tmt_pns' => null,

            'npwp' => $this->faker->optional()->numerify('###############'),

            'foto' => null,

        ];
    }
}
