<?php

namespace Database\Factories;

use App\Models\Konten;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Konten>
 */
class KontenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $jenisKonten = fake()->randomElement([

            'berita',

            'event',

            'pengumuman',

            'galeri',

        ]);

        $judul = fake()->sentence(4);

        return [

            'user_id' => User::factory(),

            'jenis_konten' => $jenisKonten,

            'judul' => $judul,

            'slug' => Str::slug($judul).'-'.fake()->unique()->numberBetween(100, 99999),

            'ringkasan' => fake()->paragraph(),

            'konten' => fake()->paragraphs(8, true),

            'thumbnail' => 'konten/thumbnail/'.fake()->uuid().'.webp',

            'tanggal_publish' => fake()->optional(0.8)->dateTimeBetween('-3 months', '+1 month'),

            'status' => fake()->randomElement(['draft', 'published']),

            'tanggal_event' => $jenisKonten === 'event'

                ? fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d')

                : null,

            'jam_mulai' => $jenisKonten === 'event'

                ? fake()->time('H:i:s')

                : null,

            'jam_selesai' => $jenisKonten === 'event'

                ? fake()->time('H:i:s')

                : null,

            'lokasi' => $jenisKonten === 'event'

                ? fake()->address()

                : null,

        ];

    }

    public function published(): static
    {

        return $this->state(fn () => [

            'status' => 'published',

            'tanggal_publish' => now(),

        ]);

    }

    public function berita(): static
    {

        return $this->state(fn () => [

            'jenis_konten' => 'berita',

        ]);

    }

    public function pengumuman(): static
    {

        return $this->state(fn () => [

            'jenis_konten' => 'pengumuman',

        ]);

    }

    public function galeri(): static
    {

        return $this->state(fn () => [

            'jenis_konten' => 'galeri',

        ]);

    }

    public function event(): static
    {

        return $this->state(fn () => [

            'jenis_konten' => 'event',

            'tanggal_event' => fake()->date(),

            'jam_mulai' => fake()->time(),

            'jam_selesai' => fake()->time(),

            'lokasi' => fake()->address(),

        ]);

    }
}
