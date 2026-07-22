<?php

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Nilai;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;

it('prevents changes to attendance and assessments after final report approval', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create(['thn_ajaran' => now()->year]);
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran, 'status' => true]);
    $subTema = SubTema::factory()->create(['tema_id' => $tema->id]);
    $guru = Guru::factory()->create();
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'tema_id' => $tema->id,
    ]);
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id, 'status' => 'aktif']);
    $absen = Absen::create(['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id, 'status' => 'hadir']);
    $komponen = KomponenPenilaian::factory()->create(['sub_tema_id' => $subTema->id]);

    RaporAkhir::create([
        'siswa_id' => $siswa->id,
        'kelas_id' => $kelas->id,
        'thn_ajaran' => $kelas->thn_ajaran,
        'status' => 'disetujui',
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('absensi.store'), [
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwal->id,
            'siswa_id' => $siswa->id,
            'status' => 'hadir',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('absensi.destroy', $absen))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('penilaian.store'), [
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwal->id,
            'absen_id' => $absen->id,
            'sub_tema_id' => $subTema->id,
            'komponen_penilaian_id' => $komponen->id,
            'nilai' => 'A',
            'keterangan' => 'Tidak boleh tersimpan.',
        ])
        ->assertForbidden();

    expect(Nilai::count())->toBe(0);
});
