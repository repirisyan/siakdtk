<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SubTema;
use App\Models\Tema;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tema = Tema::where('status', true)->orderBy('id')->firstOrFail();
        $gurus = Guru::query()->orderBy('id')->get();
        $subTemas = SubTema::where('tema_id', $tema->id)->orderBy('id')->get();

        Kelas::active()->orderBy('id')->get()->each(function (Kelas $kelas, int $kelasIndex) use ($tema, $gurus, $subTemas): void {
            $subTemas->each(function (SubTema $subTema, int $subTemaIndex) use ($kelas, $kelasIndex, $tema, $gurus): void {
                $tanggal = now()->startOfMonth()->addDays(($kelasIndex * 7) + $subTemaIndex)->toDateString();

                Jadwal::firstOrCreate(
                    ['kelas_id' => $kelas->id, 'sub_tema_id' => $subTema->id, 'tanggal' => $tanggal],
                    [
                        'guru_id' => $gurus[($kelasIndex + $subTemaIndex) % $gurus->count()]->id,
                        'tema_id' => $tema->id,
                        'jam_mulai' => '08:00:00',
                        'jam_selesai' => '10:00:00',
                    ],
                );
            });
        });
    }
}
