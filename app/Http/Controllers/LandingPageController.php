<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Konten;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\Tema;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        $published = fn () => Konten::published()->with('galeris:id,konten_id,gambar,caption,urutan');

        $galleryItems = $published()
            ->where('jenis_konten', 'galeri')
            ->latest('tanggal_publish')
            ->limit(6)
            ->get()
            ->flatMap(fn (Konten $konten) => $konten->galeris)
            ->take(12)
            ->values();

        return Inertia::render('Welcome', [
            'school' => SchoolSetting::current(),
            'hero' => $published()
                ->whereNotNull('thumbnail')
                ->latest('tanggal_publish')
                ->first(['id', 'judul', 'thumbnail']),
            'profile' => $published()
                ->where('slug', 'profil-sekolah')
                ->first(['id', 'judul', 'ringkasan', 'konten']),
            'announcements' => $published()
                ->where('jenis_konten', 'pengumuman')
                ->latest('tanggal_publish')
                ->limit(5)
                ->get(['id', 'judul', 'slug', 'ringkasan', 'tanggal_publish']),
            'news' => $published()
                ->where('jenis_konten', 'berita')
                ->latest('tanggal_publish')
                ->limit(6)
                ->get(['id', 'judul', 'slug', 'ringkasan', 'thumbnail', 'tanggal_publish']),
            'events' => $published()
                ->where('jenis_konten', 'event')
                ->whereDate('tanggal_event', '>=', today())
                ->orderBy('tanggal_event')
                ->limit(6)
                ->get(['id', 'judul', 'slug', 'ringkasan', 'thumbnail', 'tanggal_event', 'jam_mulai', 'lokasi']),
            'galleryItems' => $galleryItems,
            'statistics' => [
                'siswa' => Siswa::where('status', 'aktif')->count(),
                'guru' => Guru::whereHas('user', fn ($query) => $query->where('status', true))->count(),
                'kelas' => Kelas::active()->count(),
                'tema' => Tema::active()->whereHas('jadwal.kelas', fn ($query) => $query->where('thn_ajaran', now()->year))->count(),
            ],
        ]);
    }

    public function showNews(Konten $konten)
    {
        abort_unless(
            $konten->jenis_konten === 'berita'
                && $konten->status === 'published'
                && (! $konten->tanggal_publish || $konten->tanggal_publish->isPast()),
            404,
        );

        return Inertia::render('Berita/Show', [
            'berita' => $konten->load('user:id,name'),
        ]);
    }
}
