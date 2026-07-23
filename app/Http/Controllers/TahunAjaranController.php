<?php

namespace App\Http\Controllers;

use App\Http\Requests\TahunAjaranRequest;
use App\Models\TahunAjaran;
use App\Models\User;
use Inertia\Inertia;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $this->currentAcademicManager();
        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');

        $tahunAjarans = TahunAjaran::query()
            ->withCount('kelas')
            ->when($search, fn ($query, $search) => $query->where('tahun_ajaran', 'like', "%{$search}%"))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('TahunAjaran/Index', [
            'tahunAjarans' => $tahunAjarans,
            'filters' => compact('search', 'sort', 'direction'),
        ]);
    }

    public function create()
    {
        $this->currentAcademicManager();

        return Inertia::render('TahunAjaran/Create');
    }

    public function store(TahunAjaranRequest $request)
    {
        TahunAjaran::create($request->validated());

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function show(TahunAjaran $tahunAjaran)
    {
        $this->currentAcademicManager();

        return Inertia::render('TahunAjaran/Show', [
            'tahunAjaran' => $tahunAjaran->loadCount('kelas'),
        ]);
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        $this->currentAcademicManager();

        return Inertia::render('TahunAjaran/Edit', ['tahunAjaran' => $tahunAjaran]);
    }

    public function update(TahunAjaranRequest $request, TahunAjaran $tahunAjaran)
    {
        $data = $request->validated();

        $tahunAjaran->update($data);
        $tahunAjaran->kelas()->update(['thn_ajaran' => $data['tahun_ajaran']]);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        $this->currentAcademicManager();

        if ($tahunAjaran->kelas()->exists()) {
            return redirect()->route('tahun-ajaran.index')->with('error', 'Tahun ajaran sudah digunakan oleh kelas dan tidak dapat dihapus.');
        }

        $tahunAjaran->delete();

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    private function currentAcademicManager(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');
        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Akademik'), 403);

        return $user;
    }
}
