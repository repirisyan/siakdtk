<?php

use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Konten;
use App\Models\MasterKomponenPenilaian;
use App\Models\Nilai;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use App\Models\Spp;
use App\Models\SubTema;
use App\Models\Tema;
use Database\Seeders\DatabaseSeeder;

it('seeds a complete and connected demo dataset for application menus', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Kelas::active()->count())->toBeGreaterThan(0)
        ->and(Tema::where('status', true)->count())->toBeGreaterThan(0)
        ->and(SubTema::count())->toBeGreaterThan(0)
        ->and(MasterKomponenPenilaian::active()->count())->toBeGreaterThan(0)
        ->and(KomponenPenilaian::where('status', true)->count())->toBeGreaterThan(0)
        ->and(Siswa::where('status', 'aktif')->count())->toBeGreaterThan(0)
        ->and(Jadwal::count())->toBeGreaterThan(0)
        ->and(Absen::count())->toBeGreaterThan(0)
        ->and(Nilai::count())->toBeGreaterThan(0)
        ->and(RaporAkhir::count())->toBeGreaterThan(0)
        ->and(JenisPembayaran::active()->count())->toBeGreaterThan(0)
        ->and(Spp::count())->toBeGreaterThan(0)
        ->and(Konten::where('status', 'published')->count())->toBeGreaterThan(0);

    $jadwal = Jadwal::with('tema')->firstOrFail();
    expect($jadwal->tema)->not->toBeNull();

    $absen = Absen::with(['siswa', 'jadwal'])->firstOrFail();
    expect($absen->siswa->kelas_id)->toBe($absen->jadwal->kelas_id);
});
