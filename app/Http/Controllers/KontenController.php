<?php

namespace App\Http\Controllers;

use App\Http\Requests\KontenRequest;
use App\Models\Konten;
use App\Models\User;
use App\Services\KontenImageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class KontenController extends Controller
{
    public function index()
    {
        $this->currentKontenUser();

        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $jenisKonten = request()->query('jenis_konten');
        $status = request()->query('status');
        $allowedSorts = ['id', 'judul', 'jenis_konten', 'status', 'tanggal_publish', 'created_at'];
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $kontens = Konten::with('user:id,name,email')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('jenis_konten', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($jenisKonten, fn ($query) => $query->where('jenis_konten', $jenisKonten))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when(in_array($sort, $allowedSorts), fn ($query) => $query->orderBy($sort, $direction), fn ($query) => $query->orderBy('id', $direction))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Konten/Index', [
            'kontens' => $kontens,
            'filters' => compact('search', 'sort', 'direction', 'jenisKonten', 'status'),
        ]);
    }

    public function create()
    {
        $this->currentKontenUser();

        return Inertia::render('Konten/Create');
    }

    public function store(KontenRequest $request, KontenImageService $imageService)
    {
        $user = $this->currentKontenUser();
        $data = $request->validated();

        DB::transaction(function () use ($request, $imageService, $user, $data) {
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $imageService->store($request->file('thumbnail'), 'konten/thumbnails');
            }

            Konten::create([
                ...$data,
                'user_id' => $user->id,
                'slug' => $this->uniqueSlug($data['judul']),
            ]);
        });

        return redirect()->route('konten.index')->with('success', 'Konten berhasil dibuat.');
    }

    public function show(Konten $konten)
    {
        $this->currentKontenUser();

        return Inertia::render('Konten/Show', [
            'konten' => $konten->load(['user:id,name,email', 'galeris']),
        ]);
    }

    public function edit(Konten $konten)
    {
        $this->currentKontenUser();

        return Inertia::render('Konten/Edit', [
            'konten' => $konten->load(['user:id,name,email', 'galeris']),
        ]);
    }

    public function update(KontenRequest $request, Konten $konten, KontenImageService $imageService)
    {
        $this->currentKontenUser();
        $data = $request->validated();

        DB::transaction(function () use ($request, $imageService, $konten, $data) {
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $imageService->store($request->file('thumbnail'), 'konten/thumbnails', $konten->thumbnail);
            }

            if ($data['judul'] !== $konten->judul) {
                $data['slug'] = $this->uniqueSlug($data['judul'], $konten->id);
            }

            $konten->update($data);
        });

        return redirect()->route('konten.index')->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(Konten $konten, KontenImageService $imageService)
    {
        $this->currentKontenUser();

        DB::transaction(function () use ($konten, $imageService) {
            $konten->load('galeris');
            $imageService->delete($konten->thumbnail);

            foreach ($konten->galeris as $galeri) {
                $imageService->delete($galeri->gambar);
            }

            $konten->delete();
        });

        return redirect()->route('konten.index')->with('success', 'Konten berhasil dihapus.');
    }

    private function uniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $base = Str::slug($judul) ?: 'konten';
        $slug = $base;
        $counter = 2;

        while (Konten::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
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
