<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = $this->faker->name();

        return [

            'user_id' => User::factory()->orangTua(),

            'kelas_id' => Kelas::factory(),

            'tanggal_registrasi' => $this->faker->date(),

            'status' => 'aktif',

            'approved_by' => null,

            'approved_at' => now(),

            'nis' => $this->faker->unique()->numerify('########'),

            'nisn' => $this->faker->unique()->numerify('############'),

            'nik' => $this->faker->unique()->numerify('################'),

            'nomor_kk' => $this->faker->numerify('################'),

            'nama' => $nama,

            'tmp_lahir' => $this->faker->city(),

            'tgl_lahir' => $this->faker->date(

                'Y-m-d',

                '-5 years'

            ),

            'jk' => $this->faker->randomElement([

                'L',

                'P',

            ]),

            'agama' => $this->faker->randomElement([

                'Islam',

                'Kristen',

                'Katolik',

                'Hindu',

                'Buddha',

                'Konghucu',

            ]),

            'tinggi_bdn' => $this->faker->randomFloat(

                2,

                90,

                150

            ),

            'berat_bdn' => $this->faker->randomFloat(

                2,

                15,

                60

            ),

            'anak_ke' => $this->faker->numberBetween(

                1,

                5

            ),

            'jml_sdr' => $this->faker->numberBetween(

                0,

                5

            ),

            'nama_pgl' => $this->faker->firstName(),

            'alamat' => $this->faker->address(),

            'nama_ayah' => $this->faker->name('male'),

            'nohp_ayah' => $this->faker->numerify('08#############'),

            'ttl_ayah' => $this->faker->date(),

            'agama_ayah' => 'Islam',

            'pekerjaan' => $this->faker->randomElement([

                'Guru',

                'Wiraswasta',

                'Pedagang',

                'Karyawan Swasta',

                'PNS',

                'Petani',

                'Buruh',

            ]),

            'penghasilan' => $this->faker->randomElement([

                '< 1 juta',

                '1 - 3 juta',

                '3 - 5 juta',

                '> 5 juta',

            ]),

            'nama_ibu' => $this->faker->name('female'),

            'nohp_ibu' => $this->faker->numerify('08#############'),

            'ttl_ibu' => $this->faker->date(),

            'agama_ibu' => 'Islam',

            'pekerjaan_ibu' => $this->faker->randomElement([

                'Guru',

                'Wiraswasta',

                'Pedagang',

                'Karyawan Swasta',

                'PNS',

                'Petani',

                'Buruh',

                'Ibu Rumah Tangga',

            ]),

            'penghasilan_ibu' => $this->faker->randomElement([

                '< 1 juta',

                '1 - 3 juta',

                '3 - 5 juta',

                '> 5 juta',

            ]),

            'nama_wali' => $this->faker->optional()->name(),

        ];
    }
}
