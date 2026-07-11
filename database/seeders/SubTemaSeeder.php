<?php

namespace Database\Seeders;

use App\Models\SubTema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubTemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubTema::factory(20)->create();
    }
}
