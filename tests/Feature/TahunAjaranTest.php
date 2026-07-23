<?php

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('academic managers can manage academic years', function () {
    $admin = User::factory()->withRole('Admin')->create();

    $this->actingAs($admin)
        ->post(route('tahun-ajaran.store'), ['tahun_ajaran' => 2026])
        ->assertRedirect(route('tahun-ajaran.index'))
        ->assertSessionHas('success');

    $tahunAjaran = TahunAjaran::where('tahun_ajaran', 2026)->firstOrFail();

    $this->actingAs($admin)
        ->get(route('tahun-ajaran.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('TahunAjaran/Index')
            ->where('tahunAjarans.data.0.id', $tahunAjaran->id));
});

test('a class uses the selected academic year and prevents its deletion', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $tahunAjaran = TahunAjaran::factory()->create(['tahun_ajaran' => 2027]);

    $this->actingAs($admin)
        ->post(route('kelas.store'), [
            'nama_kelas' => 'TK A Melati',
            'tahun_ajaran_id' => $tahunAjaran->id,
            'semester' => 1,
        ])
        ->assertRedirect(route('kelas.index'));

    $kelas = Kelas::where('nama_kelas', 'TK A Melati')->firstOrFail();

    expect($kelas->tahun_ajaran_id)->toBe($tahunAjaran->id)
        ->and((int) $kelas->thn_ajaran)->toBe(2027)
        ->and($kelas->tahunAjaran->is($tahunAjaran))->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('tahun-ajaran.destroy', $tahunAjaran))
        ->assertRedirect(route('tahun-ajaran.index'))
        ->assertSessionHas('error');

    expect($tahunAjaran->fresh()->exists)->toBeTrue();
});
