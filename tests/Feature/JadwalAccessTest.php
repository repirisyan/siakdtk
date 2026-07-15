<?php

use App\Models\Guru;
use App\Models\Jadwal;
use Inertia\Testing\AssertableInertia as Assert;

it('only shows a teacher their own schedules', function () {
    $guru = Guru::factory()->create();
    $otherGuru = Guru::factory()->create();
    $jadwalMilikGuru = Jadwal::factory()->create(['guru_id' => $guru->id]);
    $jadwalGuruLain = Jadwal::factory()->create(['guru_id' => $otherGuru->id]);

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
