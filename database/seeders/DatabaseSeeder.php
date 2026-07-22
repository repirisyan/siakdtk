<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SchoolSettingSeeder::class,
            KelasSeeder::class,
            TemaSeeder::class,
            MasterKomponenPenilaianSeeder::class,
            SubTemaSeeder::class,
            KomponenPenilaianSeeder::class,
            GuruSeeder::class,
            SiswaSeeder::class,
            JadwalSeeder::class,
            AbsenSeeder::class,
            NilaiSeeder::class,
            RaporSeeder::class,
            RaporAkhirSeeder::class,
            JenisPembayaranSeeder::class,
            SppSeeder::class,
            KontenSeeder::class,
        ]);
    }
}
