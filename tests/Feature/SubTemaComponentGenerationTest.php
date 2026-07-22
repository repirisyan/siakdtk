<?php

use App\Models\MasterKomponenPenilaian;
use App\Models\Role;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;

it('copies active master components when a sub tema is created', function () {
    $role = Role::query()->firstOrCreate(['role_name' => 'Staff Akademik']);
    $user = User::factory()->create(['role_id' => $role->id, 'email_verified_at' => now()]);
    $tema = Tema::factory()->create();

    MasterKomponenPenilaian::factory()->create([
        'nama_komponen' => 'Bahasa dan Literasi',
        'deskripsi' => 'Kemampuan anak berkomunikasi melalui bahasa.',
        'status' => true,
    ]);
    MasterKomponenPenilaian::factory()->create([
        'nama_komponen' => 'Tidak Digunakan',
        'status' => false,
    ]);

    $this->actingAs($user)->post(route('sub-tema.store'), [
        'tema_id' => $tema->id,
        'nama_sub_tema' => 'Lingkungan Sekolah',
        'deskripsi' => 'Pengenalan lingkungan sekolah.',
    ])->assertRedirect(route('sub-tema.index', ['tema_id' => $tema->id]));

    $subTema = SubTema::query()->where('nama_sub_tema', 'Lingkungan Sekolah')->firstOrFail();

    expect($subTema->komponenPenilaians()->pluck('nama_komponen')->all())
        ->toBe(['Bahasa dan Literasi'])
        ->and($subTema->komponenPenilaians()->firstOrFail()->deskripsi)
        ->toBe('Kemampuan anak berkomunikasi melalui bahasa.');
});
