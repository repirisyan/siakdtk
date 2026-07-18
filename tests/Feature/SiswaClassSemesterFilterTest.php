<?php

use App\Models\Kelas;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('student class options are filtered by academic year and semester', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $semesterSatu = Kelas::factory()->create([
        'thn_ajaran' => now()->year,
        'semester' => 1,
    ]);
    Kelas::factory()->create([
        'thn_ajaran' => now()->year,
        'semester' => 2,
    ]);

    $this->actingAs($admin)
        ->get(route('siswa.create', [
            'thn_ajaran' => now()->year,
            'semester' => 1,
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Siswa/Create')
            ->has('kelas', 1)
            ->where('kelas.0.id', $semesterSatu->id)
            ->where('kelas.0.semester', 1));
});
