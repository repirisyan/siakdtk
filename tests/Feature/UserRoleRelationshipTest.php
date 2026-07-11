<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;

test('admin user can be identified by role', function () {
    $role = Role::create(['role_name' => 'Admin']);
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->hasRole('Admin'))->toBeTrue()
        ->and($role->users)->toHaveCount(1)
        ->and($role->users->first()->is($user))->toBeTrue();
});

test('user has one guru profile', function () {
    $user = User::factory()->create();
    $guru = Guru::create([
        'user_id' => $user->id,
        'nama' => 'Guru Test',
        'nip' => '198001012026011001',
        'tmp_lhr' => 'Jakarta',
        'tgl_lahir' => '1980-01-01',
        'alamat' => 'Jakarta',
    ]);

    expect($user->fresh()->guru->is($guru))->toBeTrue()
        ->and($guru->user->is($user))->toBeTrue();
});

test('user has one siswa profile', function () {
    $user = User::factory()->create();
    $kelas = Kelas::create([
        'nama_kelas' => 'Kelas 1',
        'thn_ajaran' => 2026,
    ]);
    $siswa = Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nik' => '1234567890123456',
        'nomor_kk' => '1234567890123456',
        'nama' => 'Siswa Test',
        'tmp_lahir' => 'Jakarta',
        'tgl_lahir' => '2015-01-01',
        'jk' => 'Laki-laki',
        'agama' => 'Islam',
        'alamat' => 'Jakarta',
    ]);

    expect($user->fresh()->siswa->is($siswa))->toBeTrue()
        ->and($siswa->user->is($user))->toBeTrue();
});
