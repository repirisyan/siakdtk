<?php

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores multiple activity photos for one assessment', function () {
    Storage::fake('public');

    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran]);
    $subTema = SubTema::factory()->create(['tema_id' => $tema->id]);
    $guru = Guru::factory()->create();
    $siswa = Siswa::factory()->create(['kelas_id' => $kelas->id]);
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'tema_id' => $tema->id,
    ]);
    $absen = Absen::create([
        'siswa_id' => $siswa->id,
        'jadwal_id' => $jadwal->id,
        'status' => 'hadir',
    ]);
    $komponen = KomponenPenilaian::factory()->create(['sub_tema_id' => $subTema->id]);

    $this->actingAs($admin)
        ->post(route('penilaian.store'), [
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwal->id,
            'absen_id' => $absen->id,
            'sub_tema_id' => $subTema->id,
            'komponen_penilaian_id' => $komponen->id,
            'nilai' => 'A',
            'keterangan' => 'Aktif mengikuti kegiatan.',
            'foto_kegiatan' => [
                UploadedFile::fake()->image('kegiatan-satu.jpg'),
                UploadedFile::fake()->image('kegiatan-dua.png'),
            ],
        ])
        ->assertRedirect(route('penilaian.index', [
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwal->id,
        ]));

    $nilai = $absen->nilais()->firstOrFail()->load('fotoKegiatans');

    expect($nilai->fotoKegiatans)->toHaveCount(2);
    $nilai->fotoKegiatans->each(fn ($foto) => Storage::disk('public')->assertExists($foto->path));
});
