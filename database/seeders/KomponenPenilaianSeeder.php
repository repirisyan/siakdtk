<?php

namespace Database\Seeders;

use App\Models\KomponenPenilaian;
use App\Models\SubTema;
use Illuminate\Database\Seeder;

class KomponenPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        SubTema::query()->orderBy('id')->get()->each(function (SubTema $subTema): void {
            collect(['Kemandirian', 'Komunikasi', 'Kreativitas'])->each(function (string $nama) use ($subTema): void {
                KomponenPenilaian::updateOrCreate(
                    ['sub_tema_id' => $subTema->id, 'nama_komponen' => $nama],
                    ['deskripsi' => "Penilaian {$nama} pada {$subTema->nama_sub_tema}.", 'status' => true],
                );
            });
        });
    }
}
