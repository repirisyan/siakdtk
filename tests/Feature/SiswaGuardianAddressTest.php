<?php

use App\Models\Siswa;

test('a student stores the guardian administrative address', function () {
    $siswa = Siswa::factory()->create();

    $siswa->update([
        'desa_wali' => 'Sukamaju',
        'kecamatan_wali' => 'Cianjur',
        'kabupaten_wali' => 'Cianjur',
        'provinsi_wali' => 'Jawa Barat',
    ]);

    $this->assertDatabaseHas('siswas', [
        'id' => $siswa->id,
        'desa_wali' => 'Sukamaju',
        'kecamatan_wali' => 'Cianjur',
        'kabupaten_wali' => 'Cianjur',
        'provinsi_wali' => 'Jawa Barat',
    ]);
});
