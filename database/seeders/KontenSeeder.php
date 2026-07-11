<?php

namespace Database\Seeders;

use App\Models\Konten;
use Illuminate\Database\Seeder;

class KontenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Konten::factory()->count(50)->published()->create();

        Konten::factory()->event()->published()->count(5)->create();
    }
}
