<?php

namespace Database\Seeders;

use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class AbsenSeeder extends Seeder
{
    public function run(): void
    {
        Jadwal::query()->orderBy('id')->get()->each(function (Jadwal $jadwal): void {
            Siswa::query()->where('kelas_id', $jadwal->kelas_id)->where('status', 'aktif')->orderBy('id')->get()
                ->each(function (Siswa $siswa) use ($jadwal): void {
                    $status = $siswa->id % 5 === 0 ? 'izin' : 'hadir';

                    Absen::firstOrCreate(
                        ['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id],
                        ['status' => $status, 'keterangan' => $status === 'hadir' ? null : 'Izin keperluan keluarga.'],
                    );
                });
        });
    }
}
