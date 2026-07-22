<?php

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lists each student once in assessment summary', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran]);
    $subTema = SubTema::factory()->create(['tema_id' => $tema->id]);
    $guru = Guru::factory()->create();
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);

    foreach (range(1, 2) as $day) {
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'tema_id' => $tema->id,
            'tanggal' => "2026-07-0{$day}",
        ]);
        Absen::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'hadir',
        ]);
    }

    $this->actingAs($admin)
        ->get(route('penilaian.index', [
            'kelas_id' => $kelas->id,
            'summary' => 1,
            'tema_id' => $tema->id,
            'sub_tema_id' => $subTema->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Penilaian/Index')
            ->has('summaryStudents', 1)
            ->where('summaryStudents.0.id', $siswa->id));
});

it('shows distinct assessment components and their descriptions', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran]);
    $subTema = SubTema::factory()->create(['tema_id' => $tema->id]);
    $guru = Guru::factory()->create();
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);
    $komponen = KomponenPenilaian::factory()->create([
        'sub_tema_id' => $subTema->id,
        'nama_komponen' => 'Motorik',
    ]);

    foreach (['Baik', 'Sangat baik'] as $index => $keterangan) {
        $jadwal = Jadwal::factory()->create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'tema_id' => $tema->id,
            'tanggal' => '2026-07-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
        ]);
        $absen = Absen::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'hadir',
        ]);
        Nilai::create([
            'absen_id' => $absen->id,
            'komponen_penilaian_id' => $komponen->id,
            'nilai' => 'A',
            'keterangan' => $keterangan,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('penilaian.summary', [
            'siswa' => $siswa,
            'kelas_id' => $kelas->id,
            'tema_id' => $tema->id,
            'sub_tema_id' => $subTema->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Penilaian/Summary')
            ->has('components', 1)
            ->where('components.0.nama_komponen', 'Motorik')
            ->where('components.0.keterangan', 'Baik; Sangat baik'));
});
