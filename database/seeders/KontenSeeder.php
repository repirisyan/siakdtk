<?php

namespace Database\Seeders;

use App\Models\Konten;
use App\Models\KontenGaleri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KontenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = User::query()->whereHas('role', fn ($query) => $query->where('role_name', 'Admin'))->value('id');

        collect([
            ['jenis_konten' => 'berita', 'judul' => 'Kegiatan Belajar Kreatif di Kelas', 'ringkasan' => 'Anak-anak belajar dengan suasana menyenangkan.'],
            ['jenis_konten' => 'pengumuman', 'judul' => 'Jadwal Pertemuan Orang Tua', 'ringkasan' => 'Pertemuan orang tua akan dilaksanakan pekan depan.'],
            ['jenis_konten' => 'event', 'judul' => 'Pentas Seni Anak', 'ringkasan' => 'Mari dukung kreativitas anak dalam pentas seni sekolah.'],
            ['jenis_konten' => 'galeri', 'judul' => 'Galeri Kegiatan Sekolah', 'ringkasan' => 'Dokumentasi kegiatan belajar dan bermain.'],
        ])->each(function (array $data) use ($adminId): void {
            $konten = Konten::updateOrCreate(
                ['slug' => Str::slug($data['judul'])],
                [
                    ...$data,
                    'user_id' => $adminId,
                    'konten' => $data['ringkasan'].' Data dummy untuk halaman publik sekolah.',
                    'status' => 'published',
                    'tanggal_publish' => now()->subDays(2),
                    'tanggal_event' => $data['jenis_konten'] === 'event' ? now()->addWeeks(2)->toDateString() : null,
                    'jam_mulai' => $data['jenis_konten'] === 'event' ? '08:00:00' : null,
                    'jam_selesai' => $data['jenis_konten'] === 'event' ? '10:00:00' : null,
                    'lokasi' => $data['jenis_konten'] === 'event' ? 'Aula TK SIAKDTK' : null,
                ],
            );

            if ($konten->jenis_konten === 'galeri') {
                foreach (range(1, 3) as $urutan) {
                    KontenGaleri::firstOrCreate(
                        ['konten_id' => $konten->id, 'urutan' => $urutan],
                        ['gambar' => "konten/gallery/dummy-{$urutan}.webp", 'caption' => "Kegiatan sekolah {$urutan}"],
                    );
                }
            }
        });
    }
}
