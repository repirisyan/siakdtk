<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

it('generates daily schedules from the selected start date without a sub tema relation', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $guru = Guru::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran, 'status' => true]);

    $this->actingAs($admin)
        ->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'tema_id' => $tema->id,
            'tanggal' => '2026-08-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jumlah_hari' => 3,
        ])
        ->assertRedirect(route('jadwal.index'));

    expect(Schema::hasColumn('jadwals', 'sub_tema_id'))->toBeFalse()
        ->and(Jadwal::query()->where('kelas_id', $kelas->id)->count())->toBe(3)
        ->and(Jadwal::query()->where('kelas_id', $kelas->id)->orderBy('tanggal')->pluck('tanggal')->all())
        ->toBe(['2026-08-10', '2026-08-11', '2026-08-12']);
});

it('skips Saturday and Sunday when generating schedules', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create();
    $guru = Guru::factory()->create();
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran, 'status' => true]);

    $this->actingAs($admin)
        ->post(route('jadwal.store'), [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'tema_id' => $tema->id,
            'tanggal' => '2026-08-07',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jumlah_hari' => 5,
            'skip_sabtu' => true,
            'skip_minggu' => true,
        ])
        ->assertRedirect(route('jadwal.index'))
        ->assertSessionHas('success', '3 jadwal berhasil dibuat.');

    expect(Jadwal::query()->where('kelas_id', $kelas->id)->orderBy('tanggal')->pluck('tanggal')->all())
        ->toBe(['2026-08-07', '2026-08-10', '2026-08-11']);
});
