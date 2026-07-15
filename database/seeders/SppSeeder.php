<?php

namespace Database\Seeders;

use App\Models\JenisPembayaran;
use App\Models\Siswa;
use App\Models\Spp;
use App\Models\SppPembayaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class SppSeeder extends Seeder
{
    public function run(): void
    {
        $penerimaId = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Staff Administrasi'))->value('id');
        $bulanIni = now()->startOfMonth();

        Siswa::query()->with('kelas')->where('status', 'aktif')->get()->each(function (Siswa $siswa, int $index) use ($penerimaId, $bulanIni): void {
            JenisPembayaran::active()->orderBy('id')->get()->each(function (JenisPembayaran $jenis, int $jenisIndex) use ($siswa, $penerimaId, $bulanIni, $index): void {
                $tagihan = Spp::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'jenis_pembayaran' => $jenis->nama_jenis,
                        'tanggal_tagihan' => $bulanIni->toDateString(),
                    ],
                    [
                        'thn_ajaran' => $siswa->kelas->thn_ajaran,
                        'jenis_pembayaran_id' => $jenis->id,
                        'nominal' => $jenisIndex === 0 ? 300000 : 150000,
                        'jatuh_tempo' => $bulanIni->copy()->addDays(10)->toDateString(),
                        'keterangan' => "Tagihan {$jenis->nama_jenis} periode {$bulanIni->translatedFormat('F Y')}.",
                    ],
                );

                if (($index + $jenisIndex) % 3 !== 0) {
                    SppPembayaran::updateOrCreate(
                        ['pembayaran_id' => $tagihan->id, 'tanggal_bayar' => $bulanIni->copy()->addDays(5)->toDateString()],
                        [
                            'jumlah_bayar' => $index % 2 === 0 ? $tagihan->nominal : $tagihan->nominal / 2,
                            'metode_pembayaran' => 'manual',
                            'status_verifikasi' => 'approved',
                            'received_by' => $penerimaId,
                            'verified_by' => $penerimaId,
                            'verified_at' => now(),
                            'keterangan' => 'Pembayaran dummy telah diverifikasi.',
                        ],
                    );
                }
            });
        });
    }
}
