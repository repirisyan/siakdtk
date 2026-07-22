<?php

use App\Models\Siswa;

test('a student stores the guardian administrative address', function () {
    $siswa = Siswa::factory()->create();

    $siswa->update([
        'desa' => 'Sukamaju',
        'kecamatan' => 'Cianjur',
        'kabupaten' => 'Cianjur',
        'provinsi' => 'Jawa Barat',
    ]);

    $this->assertDatabaseHas('siswas', [
        'id' => $siswa->id,
        'desa' => 'Sukamaju',
        'kecamatan' => 'Cianjur',
        'kabupaten' => 'Cianjur',
        'provinsi' => 'Jawa Barat',
    ]);
});
