<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        collect([now()->year, now()->year - 1])->each(
            fn (int $tahun) => TahunAjaran::firstOrCreate(['tahun_ajaran' => $tahun]),
        );
    }
}
