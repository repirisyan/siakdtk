<?php

namespace Database\Seeders;

use App\Models\MasterKomponenPenilaian;
use Illuminate\Database\Seeder;

class MasterKomponenPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['nama_komponen' => 'Kemandirian', 'deskripsi' => 'Kemampuan anak melakukan kegiatan secara mandiri.'],
            ['nama_komponen' => 'Komunikasi', 'deskripsi' => 'Kemampuan anak menyampaikan gagasan dan berinteraksi.'],
            ['nama_komponen' => 'Kreativitas', 'deskripsi' => 'Kemampuan anak bereksplorasi dan menghasilkan karya.'],
        ])->each(fn (array $masterKomponen) => MasterKomponenPenilaian::updateOrCreate(
            ['nama_komponen' => $masterKomponen['nama_komponen']],
            [...$masterKomponen, 'status' => true],
        ));
    }
}
