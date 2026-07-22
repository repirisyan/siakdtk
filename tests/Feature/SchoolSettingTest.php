<?php

use App\Models\SchoolSetting;
use App\Models\User;

test('an administrator can update the school administrative address', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $setting = SchoolSetting::factory()->create();

    $this->actingAs($admin)
        ->put(route('school-settings.update'), [
            'nama_sekolah' => $setting->nama_sekolah,
            'alamat' => 'Jl. Pendidikan Anak No. 1',
            'desa' => 'Sukamaju',
            'kecamatan' => 'Cianjur',
            'kabupaten' => 'Cianjur',
            'provinsi' => 'Jawa Barat',
            'nomor_telepon' => $setting->nomor_telepon,
            'email' => $setting->email,
            'website' => $setting->website,
            'visi' => $setting->visi,
            'misi' => $setting->misi,
            'tentang' => $setting->tentang,
            'sejarah_singkat' => $setting->sejarah_singkat,
            'template_deskripsi_hasil_akhir_rapor' => '{{ nama_siswa }} menunjukkan perkembangan pada {{ tema }}.',
            'tagline' => $setting->tagline,
            'pendaftaran_dibuka' => true,
        ])
        ->assertRedirect(route('school-settings.edit'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('school_settings', [
        'id' => $setting->id,
        'desa' => 'Sukamaju',
        'kecamatan' => 'Cianjur',
        'kabupaten' => 'Cianjur',
        'provinsi' => 'Jawa Barat',
        'template_deskripsi_hasil_akhir_rapor' => '{{ nama_siswa }} menunjukkan perkembangan pada {{ tema }}.',
    ]);
});
