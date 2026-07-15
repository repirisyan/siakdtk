<?php

namespace Database\Seeders;

use App\Models\Absen;
use App\Models\KomponenPenilaian;
use App\Models\Nilai;
use Illuminate\Database\Seeder;

class NilaiSeeder extends Seeder
{
    public function run(): void
    {
        Absen::query()->with('jadwal')->where('status', 'hadir')->orderBy('id')->get()->each(function (Absen $absen): void {
            KomponenPenilaian::query()->where('sub_tema_id', $absen->jadwal->sub_tema_id)->where('status', true)->get()
                ->each(function (KomponenPenilaian $komponen) use ($absen): void {
                    Nilai::updateOrCreate(
                        ['absen_id' => $absen->id, 'komponen_penilaian_id' => $komponen->id],
                        [
                            'nilai' => $absen->siswa_id % 3 === 0 ? 'B' : 'A',
                            'keterangan' => "Perkembangan {$komponen->nama_komponen} terlihat baik.",
                        ],
                    );
                });
        });
    }
}
