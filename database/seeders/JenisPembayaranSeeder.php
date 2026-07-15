<?php

namespace Database\Seeders;

use App\Models\JenisPembayaran;
use Illuminate\Database\Seeder;

class JenisPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['nama_jenis' => 'SPP Bulanan', 'deskripsi' => 'Iuran pendidikan bulanan.'],
            ['nama_jenis' => 'Kegiatan Sekolah', 'deskripsi' => 'Kontribusi kegiatan pembelajaran.'],
        ])->each(fn (array $jenis) => JenisPembayaran::updateOrCreate(
            ['nama_jenis' => $jenis['nama_jenis']],
            [...$jenis, 'status' => true],
        ));
    }
}
