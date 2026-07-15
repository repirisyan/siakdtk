<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\SubTema;
use Illuminate\Database\Seeder;

class RaporSeeder extends Seeder
{
    public function run(): void
    {
        $guruId = Guru::query()->value('id');

        Siswa::query()->with('kelas')->where('status', 'aktif')->get()->each(function (Siswa $siswa) use ($guruId): void {
            SubTema::query()->whereHas('tema', fn ($query) => $query->where('status', true))->get()
                ->each(function (SubTema $subTema) use ($siswa, $guruId): void {
                    Rapor::updateOrCreate(
                        ['siswa_id' => $siswa->id, 'sub_tema_id' => $subTema->id, 'thn_ajaran' => $siswa->kelas->thn_ajaran],
                        [
                            'user_id' => $siswa->user_id,
                            'guru_id' => $guruId,
                            'tema_id' => $subTema->tema_id,
                            'keterangan' => "Ringkasan perkembangan {$siswa->nama} pada {$subTema->nama_sub_tema}.",
                            'status' => 'draft',
                        ],
                    );
                });
        });
    }
}
