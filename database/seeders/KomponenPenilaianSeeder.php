<?php

namespace Database\Seeders;

use App\Models\SubTema;
use App\Services\SubTemaKomponenPenilaianService;
use Illuminate\Database\Seeder;

class KomponenPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        SubTema::query()
            ->orderBy('id')
            ->get()
            ->each(fn (SubTema $subTema) => app(SubTemaKomponenPenilaianService::class)->generateFromMaster($subTema));
    }
}
