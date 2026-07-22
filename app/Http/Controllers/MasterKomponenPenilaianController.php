<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterKomponenPenilaianRequest;
use App\Models\MasterKomponenPenilaian;
use App\Models\User;
use Inertia\Inertia;

class MasterKomponenPenilaianController extends Controller
{
    public function index()
    {
        $this->currentManager();
        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $status = request()->query('status');

        return Inertia::render('MasterKomponenPenilaian/Index', [
            'masterKomponenPenilaians' => MasterKomponenPenilaian::query()
                ->when($search, fn ($query) => $query->where('nama_komponen', 'like', "%{$search}%"))
                ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($query) => $query->where('status', $status === 'aktif'))
                ->orderBy($sort, $direction)
                ->paginate(10)
                ->withQueryString(),
            'filters' => compact('search', 'sort', 'direction', 'status'),
        ]);
    }

    public function create()
    {
        $this->currentManager();

        return Inertia::render('MasterKomponenPenilaian/Create');
    }

    public function store(MasterKomponenPenilaianRequest $request)
    {
        MasterKomponenPenilaian::create([
            ...$request->validated(),
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('master-komponen-penilaian.index')->with('success', 'Master komponen penilaian berhasil dibuat.');
    }

    public function show(MasterKomponenPenilaian $masterKomponenPenilaian)
    {
        $this->currentManager();

        return Inertia::render('MasterKomponenPenilaian/Show', compact('masterKomponenPenilaian'));
    }

    public function edit(MasterKomponenPenilaian $masterKomponenPenilaian)
    {
        $this->currentManager();

        return Inertia::render('MasterKomponenPenilaian/Edit', compact('masterKomponenPenilaian'));
    }

    public function update(MasterKomponenPenilaianRequest $request, MasterKomponenPenilaian $masterKomponenPenilaian)
    {
        $masterKomponenPenilaian->update([
            ...$request->validated(),
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('master-komponen-penilaian.index')->with('success', 'Master komponen penilaian berhasil diperbarui.');
    }

    public function destroy(MasterKomponenPenilaian $masterKomponenPenilaian)
    {
        $this->currentManager();
        $masterKomponenPenilaian->delete();

        return redirect()->route('master-komponen-penilaian.index')->with('success', 'Master komponen penilaian berhasil dihapus.');
    }

    private function currentManager(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');
        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Akademik'), 403);

        return $user;
    }
}
