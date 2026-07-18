<?php

use App\Models\Kelas;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a class stores a semester between one and two', function () {
    $kelas = Kelas::factory()->create(['semester' => 2]);

    expect($kelas->semester)->toBe(2);

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'semester' => 2,
    ]);
});

test('semester is required when creating a class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('kelas.store'), [
            'nama_kelas' => 'TK A Melati',
            'thn_ajaran' => now()->year,
        ])
        ->assertSessionHasErrors('semester');
});

test('classes can be filtered by semester', function () {
    $user = User::factory()->create();
    $semesterSatu = Kelas::factory()->create(['semester' => 1]);
    Kelas::factory()->create(['semester' => 2]);

    $this->actingAs($user)
        ->get(route('kelas.index', ['semester' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Kelas/Index')
            ->has('kelas.data', 1)
            ->where('kelas.data.0.id', $semesterSatu->id)
            ->where('filters.semester', '1'));
});
