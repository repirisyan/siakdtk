<?php

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;

test('an academic staff member can import siswa and parent accounts from csv', function () {
    Notification::fake();

    $manager = User::factory()->withRole('Staff Akademik')->create();
    Role::firstOrCreate(['role_name' => 'Orangtua Siswa']);
    $kelas = Kelas::factory()->create(['status' => true]);
    $csv = implode("\n", [
        'name,email,password,password_confirmation,kelas_id,nik,nomor_kk,nama,tmp_lahir,tgl_lahir,jk,agama,alamat',
        "Akun Orangtua,orangtua@example.test,Password123!,Password123!,{$kelas->id},3200000000000001,3200000000000002,Nama Siswa,Cianjur,2021-01-01,L,Islam,Jl. Pendidikan Anak",
    ]);

    $this->actingAs($manager)
        ->post(route('siswa.import'), [
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
        ])
        ->assertRedirect(route('siswa.index'))
        ->assertSessionHas('success', '1 data siswa berhasil diimpor.');

    $user = User::where('email', 'orangtua@example.test')->firstOrFail();

    expect($user->hasRole('Orangtua Siswa'))->toBeTrue();
    $this->assertDatabaseHas('siswas', [
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nama' => 'Nama Siswa',
        'status' => 'aktif',
    ]);
});

test('an academic staff member can export siswa data as csv', function () {
    $manager = User::factory()->withRole('Staff Akademik')->create();
    $siswa = Siswa::factory()->create(['nama' => '=Nama Formula']);

    $response = $this->actingAs($manager)->get(route('siswa.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertStreamed();
    expect($response->streamedContent())
        ->toContain('name,email,kelas_id,nis,nisn,nik')
        ->toContain("'=Nama Formula");
    expect($siswa->fresh()->exists)->toBeTrue();
});
