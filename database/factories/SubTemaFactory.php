<?php

namespace Database\Factories;

use App\Models\SubTema;
use App\Models\Tema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubTema>
 */
class SubTemaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SubTema::class;

    protected array $subTemas = [

        'Identitas Diri',

        'Anggota Tubuh',

        'Kesukaanku',

        'Rumahku',

        'Sekolahku',

        'Tetanggaku',

        'Binatang Darat',

        'Binatang Air',

        'Binatang Udara',

        'Kendaraan Darat',

        'Kendaraan Laut',

        'Kendaraan Udara',

        'Tanaman Hias',

        'Tanaman Buah',

        'Cuaca',

        'Musim',

        'Profesi',

        'Alat Komunikasi',

    ];

    public function definition(): array

    {

        return [

            'tema_id' => Tema::factory(),

            'nama_sub_tema' => $this->faker->randomElement($this->subTemas),

            'deskripsi' => $this->faker->sentence(),

        ];

    }
}
