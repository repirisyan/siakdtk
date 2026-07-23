<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Kelas $kelas): void {
            $tahunAjaran = TahunAjaran::firstOrCreate([
                'tahun_ajaran' => $kelas->thn_ajaran,
            ]);

            $kelas->update(['tahun_ajaran_id' => $tahunAjaran->id]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'nama_kelas' => 'TK '.$this->faker->unique()->bothify('??-##'),

            'thn_ajaran' => $this->faker->numberBetween(

                2025,

                2030

            ),

            'semester' => $this->faker->numberBetween(1, 2),

            'status' => true,

        ];
    }
}
