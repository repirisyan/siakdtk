<?php

namespace Database\Seeders;

use App\Models\Tema;
use Illuminate\Database\Seeder;

class TemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahunAjaran = now()->year;

        collect([
            ['nama_tema' => 'Lingkunganku', 'thn_ajaran' => $tahunAjaran, 'status' => true],
            ['nama_tema' => 'Diriku', 'thn_ajaran' => $tahunAjaran, 'status' => false],
            ['nama_tema' => 'Keluargaku', 'thn_ajaran' => $tahunAjaran - 1, 'status' => false],
        ])->each(fn (array $tema) => Tema::updateOrCreate(
            ['nama_tema' => $tema['nama_tema'], 'thn_ajaran' => $tema['thn_ajaran']],
            ['status' => $tema['status']],
        ));
    }
}
