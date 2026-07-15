<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\RaporAkhir;
use App\Models\RaporAkhirDetail;
use App\Models\Siswa;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Seeder;

class RaporAkhirSeeder extends Seeder
{
    public function run(): void
    {
        $guruId = Guru::query()->value('id');
        $kepsekId = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Kepsek'))->value('id');

        Siswa::query()->with('kelas')->where('status', 'aktif')->get()->each(function (Siswa $siswa) use ($guruId, $kepsekId): void {
            $status = $siswa->id % 4 === 0 ? 'menunggu_validasi' : 'disetujui';
            $rapor = RaporAkhir::updateOrCreate(
                ['siswa_id' => $siswa->id, 'kelas_id' => $siswa->kelas_id, 'thn_ajaran' => $siswa->kelas->thn_ajaran],
                [
                    'status' => $status,
                    'approved_by' => $status === 'disetujui' ? $kepsekId : null,
                    'approved_at' => $status === 'disetujui' ? now() : null,
                ],
            );

            Tema::query()->where('status', true)->where('thn_ajaran', $siswa->kelas->thn_ajaran)->get()->each(
                fn (Tema $tema) => RaporAkhirDetail::updateOrCreate(
                    ['rapor_akhir_id' => $rapor->id, 'tema_id' => $tema->id],
                    ['guru_id' => $guruId, 'keterangan' => "Catatan akhir {$siswa->nama} pada tema {$tema->nama_tema}."],
                ),
            );
        });
    }
}
