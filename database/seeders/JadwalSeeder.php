<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
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
        Kelas::active()->orderBy('id')->get()->each(function (Kelas $kelas, int $kelasIndex) use ($tema, $gurus): void {
            foreach (range(0, 2) as $hariKe) {
                $tanggal = now()->startOfMonth()->addDays(($kelasIndex * 7) + $hariKe)->toDateString();

                Jadwal::firstOrCreate(
                    ['kelas_id' => $kelas->id, 'tema_id' => $tema->id, 'tanggal' => $tanggal],
                    [
                        'guru_id' => $gurus[($kelasIndex + $hariKe) % $gurus->count()]->id,
                        'jam_mulai' => '08:00:00',
                        'jam_selesai' => '10:00:00',
                    ],
                );
            }
        });
    }
}
