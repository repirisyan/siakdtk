<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::query()->firstOrCreate([], [
            'nama_sekolah' => 'TK SIAKDTK',
            'tagline' => 'Tumbuh, Belajar, dan Bermain Bersama',
            'alamat' => 'Jl. Pendidikan Anak No. 1',
            'nomor_telepon' => '021-555-0100',
            'email' => 'info@siakdtk.test',
            'tentang' => 'Sistem Informasi Akademik untuk mendukung layanan pendidikan anak usia dini.',
            'pendaftaran_dibuka' => true,
        ]);
    }
}
