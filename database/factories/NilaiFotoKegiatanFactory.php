<?php

namespace Database\Factories;

use App\Models\Nilai;
use App\Models\NilaiFotoKegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NilaiFotoKegiatan> */
class NilaiFotoKegiatanFactory extends Factory
{
    protected $model = NilaiFotoKegiatan::class;

    public function definition(): array
    {
        return [
            'nilai_id' => Nilai::factory(),
            'path' => 'penilaian/kegiatan/'.fake()->uuid().'.webp',
        ];
    }
}
