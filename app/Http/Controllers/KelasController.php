<?php

namespace App\Http\Controllers;

use App\Http\Requests\KelasRequest;
use App\Http\Requests\MoveStudentsRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KelasController extends Controller
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
        $semester = request()->query('semester');

        $kelas = Kelas::query()
            ->withCount('siswa')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('thn_ajaran', 'like', "%{$search}%")
                        ->orWhere('semester', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($query) => $query->where('status', $status === 'aktif'))
            ->when(in_array((int) $semester, [1, 2], true), fn ($query) => $query->where('semester', (int) $semester))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Kelas/Index', [
            'kelas' => $kelas,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'status' => $status,
                'semester' => $semester,
            ],
            'kelasOptions' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran', 'semester']),
            'canMoveStudents' => auth()->user()?->loadMissing('role')->hasRole('Admin') ?? false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Kelas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KelasRequest $request)
    {
        Kelas::create($request->validated());

        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        return Inertia::render('Kelas/Show', ['kelas' => $kelas]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        return Inertia::render('Kelas/Edit', [
            'kelas' => $kelas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswa()->exists() || $kelas->jadwal()->exists()) {
            return redirect()->route('kelas.index')->with('error', 'Kelas sudah digunakan. Nonaktifkan kelas untuk menjaga data historis.');
        }

        $kelas->delete();

        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    public function toggleStatus(Kelas $kelas)
    {
        $kelas->update(['status' => ! $kelas->status]);

        return redirect()->route('kelas.index')->with('success', $kelas->status ? 'Kelas berhasil diaktifkan.' : 'Kelas berhasil dinonaktifkan.');
    }

    public function moveStudents(MoveStudentsRequest $request, Kelas $kelas)
    {
        $data = $request->validated();
        $totalSiswa = $kelas->siswa()->count();

        if ($totalSiswa === 0) {
            return redirect()
                ->route('kelas.index')
                ->with('success', 'Tidak ada siswa yang dapat dipindahkan.');
        }

        DB::transaction(function () use ($kelas, $data) {
            Siswa::where('kelas_id', $kelas->id)->update([
                'kelas_id' => $data['kelas_tujuan_id'],
            ]);
        });

        return redirect()
            ->route('kelas.index')
            ->with('success', 'Siswa berhasil dipindahkan ke kelas tujuan.');
    }
}
