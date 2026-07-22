<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('only shows a teacher their own schedules', function () {
    $guru = Guru::factory()->create();
    $otherGuru = Guru::factory()->create();
    $jadwalMilikGuru = Jadwal::factory()->create([
        'guru_id' => $guru->id,
        'tanggal' => now()->toDateString(),
    ]);
    $jadwalGuruLain = Jadwal::factory()->create([
        'guru_id' => $otherGuru->id,
        'tanggal' => now()->toDateString(),
    ]);

    $this->actingAs($guru->user)
        ->get(route('jadwal.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Jadwal/Index')
            ->has('jadwals.data', 1)
            ->where('jadwals.data.0.id', $jadwalMilikGuru->id));

    $this->actingAs($guru->user)
        ->get(route('jadwal.show', $jadwalGuruLain))
        ->assertForbidden();
});

it('defaults the schedule list to the current week and can clear the date filter', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $jadwalMingguIni = Jadwal::factory()->create(['tanggal' => now()->toDateString()]);
    $jadwalLama = Jadwal::factory()->create(['tanggal' => now()->subWeek()->toDateString()]);

    $this->actingAs($admin)
        ->get(route('jadwal.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Jadwal/Index')
            ->has('jadwals.data', 1)
            ->where('jadwals.data.0.id', $jadwalMingguIni->id)
            ->where('filters.tanggal_mulai', now()->startOfWeek()->toDateString())
            ->where('filters.tanggal_selesai', now()->endOfWeek()->toDateString())
            ->where('filters.show_all', false));

    $this->actingAs($admin)
        ->get(route('jadwal.index', ['show_all' => true]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('jadwals.data', 2)
            ->where('filters.tanggal_mulai', null)
            ->where('filters.tanggal_selesai', null)
            ->where('filters.show_all', true));

    expect($jadwalLama->exists)->toBeTrue();
});

it('provides the class semester in the schedule form options', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create(['semester' => 2]);

    $this->actingAs($admin)
        ->get(route('jadwal.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Jadwal/Create')
            ->where('kelas.0.id', $kelas->id)
            ->where('kelas.0.semester', 2));
});
