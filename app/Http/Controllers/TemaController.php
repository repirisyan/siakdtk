<?php

namespace App\Http\Controllers;

use App\Http\Requests\TemaRequest;
use App\Models\Tema;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $status = request()->query('status');

        $temas = Tema::query()->withCount(['subTemas', 'jadwal', 'rapor', 'raporAkhirDetails'])
            ->when($search, function ($query, $search) {
                $query->where('nama_tema', 'like', "%{$search}%");
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($query) => $query->where('status', $status === 'aktif'))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Tema/Index', [
            'temas' => $temas,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tema/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TemaRequest $request)
    {
        Tema::create([...$request->validated(), 'status' => false]);

        return redirect()->route('tema.index')->with('success', 'Tema berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tema $tema)
    {
        return Inertia::render('Tema/Show', ['tema' => $tema]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tema $tema)
    {
        return Inertia::render('Tema/Edit', [
            'tema' => $tema,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TemaRequest $request, Tema $tema)
    {
        $tema->update($request->validated());

        return redirect()->route('tema.index')->with('success', 'Tema berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tema $tema)
    {
        if ($tema->subTemas()->exists() || $tema->jadwal()->exists() || $tema->rapor()->exists() || $tema->raporAkhirDetails()->exists()) {
            return redirect()->route('tema.index')->with('error', 'Tema sudah digunakan. Nonaktifkan tema untuk menjaga data historis.');
        }

        $tema->delete();

        return redirect()->route('tema.index')->with('success', 'Tema berhasil dihapus.');
    }

    public function toggleStatus(Tema $tema)
    {
        DB::transaction(function () use ($tema) {
            Tema::where('status', true)->update(['status' => false]);
            $tema->update(['status' => true]);
        });

        return redirect()->route('tema.index')->with('success', 'Tema berhasil diaktifkan. Tema aktif sebelumnya telah dinonaktifkan.');
    }
}
