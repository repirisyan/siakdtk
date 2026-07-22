<?php

namespace App\Http\Controllers;

use App\Http\Requests\JadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Tema;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
        $showAll = request()->boolean('show_all');
        $filterData = request()->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);
        $tanggalMulai = $showAll
            ? null
            : Carbon::parse($filterData['tanggal_mulai'] ?? now()->startOfWeek(Carbon::MONDAY))->toDateString();
        $tanggalSelesai = $showAll
            ? null
            : Carbon::parse($filterData['tanggal_selesai'] ?? now()->endOfWeek(Carbon::SUNDAY))->toDateString();

        $relationSorts = ['nama_kelas', 'nama_guru', 'nama_tema'];

        $jadwals = Jadwal::with([
            'kelas:id,nama_kelas,thn_ajaran',
            'guru:id,nama,nip',
            'tema:id,nama_tema',
        ])->withCount('absens')
            ->when($this->isGuru($user), fn ($query) => $query->where('guru_id', $user->guru->id))
            ->when($tanggalMulai, fn ($query) => $query->whereDate('tanggal', '>=', $tanggalMulai))
            ->when($tanggalSelesai, fn ($query) => $query->whereDate('tanggal', '<=', $tanggalSelesai))
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
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'show_all' => $showAll,
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
        $jumlahHari = (int) ($data['jumlah_hari'] ?? 1);
        $skipSabtu = (bool) ($data['skip_sabtu'] ?? false);
        $skipMinggu = (bool) ($data['skip_minggu'] ?? false);
        $jadwalData = Arr::except($data, ['jumlah_hari', 'skip_sabtu', 'skip_minggu']);
        $guruId = $this->guruIdForCurrentUser($data);

        $created = DB::transaction(function () use ($jadwalData, $jumlahHari, $guruId, $skipSabtu, $skipMinggu): int {
            $tanggalAwal = Carbon::parse($jadwalData['tanggal']);
            $created = 0;

            foreach (range(0, $jumlahHari - 1) as $hariKe) {
                $tanggal = $tanggalAwal->copy()->addDays($hariKe);

                if (($skipSabtu && $tanggal->isSaturday()) || ($skipMinggu && $tanggal->isSunday())) {
                    continue;
                }

                Jadwal::create([
                    ...$jadwalData,
                    'guru_id' => $guruId,
                    'tanggal' => $tanggal->toDateString(),
                ]);
                $created++;
            }

            return $created;
        });

        if ($created === 0) {
            return redirect()
                ->route('jadwal.index')
                ->with('warning', 'Tidak ada jadwal dibuat karena seluruh tanggal pada rentang yang dipilih dilewati.');
        }

        return redirect()
            ->route('jadwal.index')
            ->with('success', $created === 1 ? 'Jadwal berhasil dibuat.' : "{$created} jadwal berhasil dibuat.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);

        return Inertia::render('Jadwal/Show', [
            'jadwal' => $jadwal->load(['kelas:id,nama_kelas,thn_ajaran', 'guru:id,nama,nip', 'tema:id,nama_tema']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jadwal $jadwal)
    {
        $this->authorizeJadwal($jadwal);

        return Inertia::render('Jadwal/Edit', [
            'jadwal' => $jadwal->load(['kelas:id,nama_kelas,thn_ajaran', 'guru:id,nama,nip', 'tema:id,nama_tema']),
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

        $jadwal->update([
            ...Arr::except($data, ['jumlah_hari', 'skip_sabtu', 'skip_minggu']),
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

        if ($jadwal->absens()->exists()) {
            return redirect()->route('jadwal.index')->with('error', 'Jadwal sudah memiliki data absensi dan tidak dapat dihapus.');
        }

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
            'kelas' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran', 'semester']),
            'gurus' => $canManageSchedule
                ? Guru::orderBy('nama')->get(['id', 'nama', 'nip'])
                : [],
            'temas' => Tema::active()->orderByDesc('thn_ajaran')->orderBy('nama_tema')->get(['id', 'nama_tema', 'thn_ajaran']),
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
