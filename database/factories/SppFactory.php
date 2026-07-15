<?php

namespace Database\Factories;

use App\Models\JenisPembayaran;
use App\Models\Siswa;
use App\Models\Spp;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Spp> */
class SppFactory extends Factory
{
    public function definition(): array
    {
        return [
            'siswa_id' => Siswa::factory(),
            'thn_ajaran' => now()->year,
            'jenis_pembayaran_id' => JenisPembayaran::factory(),
            'jenis_pembayaran' => 'SPP Bulanan',
            'tanggal_tagihan' => now()->startOfMonth()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'nominal' => 300000,
            'keterangan' => 'Tagihan SPP bulanan.',
        ];
    }
}
