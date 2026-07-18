<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahunAjaran = now()->year;

        collect([
            ['nama_kelas' => 'TK A Melati', 'thn_ajaran' => $tahunAjaran, 'semester' => 1, 'status' => true],
            ['nama_kelas' => 'TK A Anggrek', 'thn_ajaran' => $tahunAjaran, 'semester' => 1, 'status' => true],
            ['nama_kelas' => 'TK B Mawar', 'thn_ajaran' => $tahunAjaran, 'semester' => 2, 'status' => true],
            ['nama_kelas' => 'TK B Kenanga', 'thn_ajaran' => $tahunAjaran - 1, 'semester' => 2, 'status' => false],
        ])->each(fn (array $kelas) => Kelas::updateOrCreate(
            [
                'nama_kelas' => $kelas['nama_kelas'],
                'thn_ajaran' => $kelas['thn_ajaran'],
            ],
            [
                'semester' => $kelas['semester'],
                'status' => $kelas['status'],
            ],
        ));
    }
}
