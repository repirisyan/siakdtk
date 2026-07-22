<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubTemaRequest;
use App\Models\SubTema;
use App\Models\Tema;
use App\Services\SubTemaKomponenPenilaianService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubTemaController extends Controller
{
    public function index()
    {
        $temaId = request()->query('tema_id');
        $subTemas = SubTema::with('tema:id,nama_tema,thn_ajaran')->withCount('komponenPenilaians')->when($temaId, fn ($query) => $query->where('tema_id', $temaId))->orderBy('nama_sub_tema')->paginate(10)->withQueryString();

        return Inertia::render('SubTema/Index', ['subTemas' => $subTemas, 'temas' => Tema::orderByDesc('thn_ajaran')->orderBy('nama_tema')->get(['id', 'nama_tema', 'thn_ajaran']), 'filters' => ['tema_id' => $temaId]]);
    }

    public function create()
    {
        return Inertia::render('SubTema/Create', ['temas' => Tema::orderByDesc('thn_ajaran')->orderBy('nama_tema')->get(['id', 'nama_tema', 'thn_ajaran'])]);
    }

    public function store(SubTemaRequest $request, SubTemaKomponenPenilaianService $komponenPenilaianService)
    {
        DB::transaction(function () use ($request, $komponenPenilaianService): void {
            $subTema = SubTema::create($request->validated());
            $komponenPenilaianService->generateFromMaster($subTema);
        });

        return redirect()->route('sub-tema.index', ['tema_id' => $request->tema_id])->with('success', 'Sub tema berhasil dibuat.');
    }

    public function show(SubTema $subTema)
    {
        return Inertia::render('SubTema/Show', ['subTema' => $subTema->load('tema:id,nama_tema,thn_ajaran')]);
    }

    public function edit(SubTema $subTema)
    {
        return Inertia::render('SubTema/Edit', ['subTema' => $subTema, 'temas' => Tema::orderByDesc('thn_ajaran')->orderBy('nama_tema')->get(['id', 'nama_tema', 'thn_ajaran'])]);
    }

    public function update(SubTemaRequest $request, SubTema $subTema)
    {
        $subTema->update($request->validated());

        return redirect()->route('sub-tema.index', ['tema_id' => $subTema->tema_id])->with('success', 'Sub tema berhasil diperbarui.');
    }

    public function destroy(SubTema $subTema)
    {
        if ($subTema->komponenPenilaians()->exists()) {
            return redirect()->route('sub-tema.index', ['tema_id' => $subTema->tema_id])->with('error', 'Sub tema sudah memiliki komponen penilaian dan tidak dapat dihapus.');
        }

        $subTema->delete();

        return redirect()->route('sub-tema.index')->with('success', 'Sub tema berhasil dihapus.');
    }
}
