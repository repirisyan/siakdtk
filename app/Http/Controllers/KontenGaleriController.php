<?php

namespace App\Http\Controllers;

use App\Http\Requests\KontenGaleriRequest;
use App\Models\Konten;
use App\Models\KontenGaleri;
use App\Models\User;
use App\Services\KontenImageService;
use Illuminate\Support\Facades\DB;

class KontenGaleriController extends Controller
{
    public function store(KontenGaleriRequest $request, Konten $konten, KontenImageService $imageService)
    {
        $this->currentKontenUser();
        abort_unless($konten->jenis_konten === 'galeri', 422, 'Foto hanya dapat ditambahkan pada konten galeri.');
        $data = $request->validated();

        DB::transaction(function () use ($data, $konten, $imageService) {
            $startOrder = (int) $konten->galeris()->max('urutan');

            foreach ($data['gambar'] as $index => $gambar) {
                $konten->galeris()->create([
                    'gambar' => $imageService->store($gambar, 'konten/gallery'),
                    'caption' => $data['caption'][$index] ?? null,
                    'urutan' => $data['urutan'][$index] ?? $startOrder + $index + 1,
                ]);
            }
        });

        return redirect()->route('konten.edit', $konten)->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function destroy(Konten $konten, KontenGaleri $galeri, KontenImageService $imageService)
    {
        $this->currentKontenUser();
        abort_unless($galeri->konten_id === $konten->id, 404);

        DB::transaction(function () use ($galeri, $imageService) {
            $imageService->delete($galeri->gambar);
            $galeri->delete();
        });

        return redirect()->route('konten.edit', $konten)->with('success', 'Foto galeri berhasil dihapus.');
    }

    private function currentKontenUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');
        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Administrasi'), 403);

        return $user;
    }
}
