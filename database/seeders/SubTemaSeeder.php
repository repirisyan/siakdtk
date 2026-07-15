<?php

namespace Database\Seeders;

use App\Models\SubTema;
use App\Models\Tema;
use Illuminate\Database\Seeder;

class SubTemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tema = Tema::where('status', true)->orderBy('id')->firstOrFail();

        collect([
            'Rumah dan Sekolah',
            'Teman di Sekitar',
            'Menjaga Lingkungan',
        ])->each(fn (string $nama) => SubTema::updateOrCreate(
            ['tema_id' => $tema->id, 'nama_sub_tema' => $nama],
            ['deskripsi' => "Kegiatan pembelajaran {$nama}."],
        ));
    }
}
