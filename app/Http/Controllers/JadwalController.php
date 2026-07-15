<?php

namespace App\Http\Controllers;

use App\Http\Requests\JadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Inertia\Inertia;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = $this->currentSchedulingUser();
        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');

        $relationSorts = ['nama_kelas', 'nama_guru', 'nama_tema'];

        $jadwals = Jadwal::with([
            'kelas:id,nama_kelas,thn_ajaran',
            'guru:id,nama,nip',
            'tema:id,nama_tema',
            'subTema:id,tema_id,nama_sub_tema',
        ])
            ->when($this->isGuru($user), fn ($query) => $query->where('guru_id', $user->guru->id))
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('tanggal', 'like', "%{$search}%")
                        ->orWhereHas('kelas', function ($query) use ($search) {
                            $query->where('nama_kelas', 'like', "%{$search}%");
                        })
                        ->orWhereHas('guru', function ($query) use ($search) {
                            $query
                                ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tema', function ($query) use ($search) {
                            $query->where('nama_tema', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sort === 'nama_kelas', function ($query) use ($direction) {
                $query->orderBy(Kelas::select('nama_kelas')->whereColumn('kelas.id', 'jadwals.kelas_id'), $direction);
            })
            ->when($sort === 'nama_guru', function ($query) use ($direction) {
                $query->orderBy(Guru::select('nama')->whereColumn('gurus.id', 'jadwals.guru_id'), $direction);
            })
            ->when($sort === 'nama_tema', function ($query) use ($direction) {
                $query->orderBy(Tema::select('nama_tema')->whereColumn('temas.id', 'jadwals.tema_id'), $direction);
            })
            ->when(! in_array($sort, $relationSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Jadwal/Index', [
            'jadwals' => $jadwals,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Jadwal/Create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JadwalRequest $request)
    {
        $data = $request->validated();
        $subTema = SubTema::whereKey($data['sub_tema_id'])->whereHas('tema', fn ($query) => $query->where('status', true))->firstOrFail();

        Jadwal::create([
            ...$data,
            'tema_id' => $subTema->tema_id,
            'guru_id' => $this->guruIdForCurrentUser($data),
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);

        return Inertia::render('Jadwal/Show', [
            'jadwal' => $jadwal->load(['kelas:id,nama_kelas,thn_ajaran', 'guru:id,nama,nip', 'tema:id,nama_tema', 'subTema:id,tema_id,nama_sub_tema']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);

        return Inertia::render('Jadwal/Edit', [
            'jadwal' => $jadwal->load(['kelas:id,nama_kelas,thn_ajaran', 'guru:id,nama,nip', 'tema:id,nama_tema', 'subTema:id,tema_id,nama_sub_tema']),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JadwalRequest $request, Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);
        $data = $request->validated();
        $subTema = SubTema::whereKey($data['sub_tema_id'])->whereHas('tema', fn ($query) => $query->where('status', true))->firstOrFail();

        $jadwal->update([
            ...$data,
            'tema_id' => $subTema->tema_id,
            'guru_id' => $this->guruIdForCurrentUser($data),
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);
        $jadwal->delete();

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    private function formOptions(): array
    {
        $user = $this->currentSchedulingUser();
        $canManageSchedule = $this->canManageSchedule($user);

        return [
            'kelas' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran']),
            'gurus' => $canManageSchedule
                ? Guru::orderBy('nama')->get(['id', 'nama', 'nip'])
                : [],
            'subTemas' => SubTema::with('tema:id,nama_tema,thn_ajaran')->whereHas('tema', fn ($query) => $query->where('status', true))->orderBy('nama_sub_tema')->get(['id', 'tema_id', 'nama_sub_tema']),
            'canSelectGuru' => $canManageSchedule,
            'currentGuru' => $canManageSchedule
                ? null
                : $user->guru->only(['id', 'nama', 'nip']),
        ];
    }

    private function guruIdForCurrentUser(array $data): int
    {
        $user = $this->currentSchedulingUser();

        return $this->canManageSchedule($user)
            ? (int) $data['guru_id']
            : $user->guru->id;
    }

    private function authorizeJadwal(Jadwal $jadwal): void
    {
        $user = $this->currentSchedulingUser();

        if (! $this->canManageSchedule($user)) {
            abort_unless($jadwal->guru_id === $user->guru->id, 403);
        }
    }

    private function currentSchedulingUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing(['role', 'guru']);

        abort_unless(
            $this->canManageSchedule($user) || ($this->isGuru($user) && $user->guru),
            403,
        );

        return $user;
    }

    private function canManageSchedule(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Staff Akademik');
    }

    private function isGuru(User $user): bool
    {
        return $user->role?->role_name === 'Guru';
    }
}
