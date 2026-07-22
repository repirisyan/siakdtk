<?php

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tema;
use App\Models\User;

it('rejects deletion of a class and schedule that already have strict child records', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $guru = Guru::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran]);
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'tema_id' => $tema->id,
    ]);
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);
    Absen::create(['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id, 'status' => 'hadir']);

    $this->actingAs($admin)
        ->delete(route('kelas.destroy', $kelas))
        ->assertRedirect(route('kelas.index'))
        ->assertSessionHas('error');

    $this->actingAs($admin)
        ->delete(route('jadwal.destroy', $jadwal))
        ->assertRedirect(route('jadwal.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('kelas', ['id' => $kelas->id]);
    $this->assertDatabaseHas('jadwals', ['id' => $jadwal->id]);
});
