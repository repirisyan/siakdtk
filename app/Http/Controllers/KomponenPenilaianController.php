<?php

namespace App\Http\Controllers;

use App\Http\Requests\KomponenPenilaianRequest;
use App\Models\KomponenPenilaian;
use App\Models\SubTema;
use Inertia\Inertia;

class KomponenPenilaianController extends Controller
{
    public function index()
    {
        $subTemaId = request()->query('sub_tema_id');
        $subTema = $subTemaId
            ? SubTema::with('tema:id,nama_tema,thn_ajaran')->findOrFail($subTemaId)
            : null;

        return Inertia::render('KomponenPenilaian/Index', [
            'subTema' => $subTema,
            'komponenPenilaians' => KomponenPenilaian::with('subTema.tema:id,nama_tema')->withCount('nilais')
                ->when($subTemaId, fn ($query) => $query->where('sub_tema_id', $subTemaId))
                ->orderBy('nama_komponen')
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['sub_tema_id' => $subTemaId],
        ]);
    }

    public function create()
    {
        $subTemaId = request()->query('sub_tema_id');

        return Inertia::render('KomponenPenilaian/Create', [
            'subTema' => $subTemaId
                ? SubTema::with('tema:id,nama_tema,thn_ajaran')->findOrFail($subTemaId)
                : null,
        ]);
    }

    public function store(KomponenPenilaianRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        KomponenPenilaian::create($data);

        return redirect()
            ->route('komponen-penilaian.index', ['sub_tema_id' => $data['sub_tema_id']])
            ->with('success', 'Komponen penilaian berhasil dibuat.');
    }

    public function edit(KomponenPenilaian $komponenPenilaian)
    {
        return Inertia::render('KomponenPenilaian/Edit', [
            'komponenPenilaian' => $komponenPenilaian->load('subTema.tema:id,nama_tema,thn_ajaran'),
        ]);
    }

    public function update(KomponenPenilaianRequest $request, KomponenPenilaian $komponenPenilaian)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        $komponenPenilaian->update($data);

        return redirect()
            ->route('komponen-penilaian.index', ['sub_tema_id' => $komponenPenilaian->sub_tema_id])
            ->with('success', 'Komponen penilaian berhasil diperbarui.');
    }

    public function destroy(KomponenPenilaian $komponenPenilaian)
    {
        if ($komponenPenilaian->nilais()->exists()) {
            return back()->with('error', 'Komponen penilaian sudah digunakan dan tidak dapat dihapus.');
        }

        $subTemaId = $komponenPenilaian->sub_tema_id;
        $komponenPenilaian->delete();

        return redirect()
            ->route('komponen-penilaian.index', ['sub_tema_id' => $subTemaId])
            ->with('success', 'Komponen penilaian berhasil dihapus.');
    }
}
