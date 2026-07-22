<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaporApprovalRequest;
use App\Http\Requests\RaporRejectRequest;
use App\Http\Requests\RaporRequest;
use App\Http\Requests\RaporSubmitRequest;
use App\Models\Kelas;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RaporController extends Controller
{
    public function index()
    {
        $user = $this->currentRaporUser();
        $kelasId = request()->query('kelas_id');

        $kelas = $this->kelasForUser($user);
        $selectedKelas = null;
        $siswas = collect();
        $rapors = collect();

        if ($kelasId) {
            abort_unless($kelas->contains('id', (int) $kelasId), 403);

            $selectedKelas = Kelas::withCount('siswa')->findOrFail($kelasId);
            $siswas = Siswa::with('user:id,name,email')
                ->where('kelas_id', $selectedKelas->id)
                ->orderBy('nama')
                ->get();
            $rapors = Rapor::with([
                'siswa:id,user_id,kelas_id,nama,nis',
                'tema:id,nama_tema',
                'subTema:id,tema_id,nama_sub_tema',
                'guru:id,nama,nip',
                'validator:id,name,email',
            ])
                ->where('thn_ajaran', $selectedKelas->thn_ajaran)
                ->whereIn('siswa_id', $siswas->pluck('id'))
                ->get();
        }

        return Inertia::render('Rapor/Index', [
            'kelas' => $kelas,
            'selectedKelas' => $selectedKelas,
            'currentGuru' => $user->hasRole('Guru')
                ? $user->guru->only(['id', 'nama', 'nip'])
                : null,
            'temas' => $selectedKelas
                ? Tema::where('thn_ajaran', $selectedKelas->thn_ajaran)
                    ->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $selectedKelas->id))
                    ->with(['subTemas' => fn ($query) => $query->orderBy('nama_sub_tema')])
                    ->orderBy('nama_tema')
                    ->get(['id', 'nama_tema', 'thn_ajaran'])
                : collect(),
            'siswas' => $siswas,
            'rapors' => $rapors,
            'canManageRapor' => $user->hasRole('Guru'),
            'filters' => [
                'kelas_id' => $kelasId,
            ],
        ]);
    }

    public function store(RaporRequest $request)
    {
        $data = $request->validated();
        $user = $this->currentGuruUser();
        $kelas = $this->authorizeKelas($user, (int) $data['kelas_id']);

        abort_unless($kelas->status && Tema::active()->whereKey($data['tema_id'])->exists(), 403);
        abort_unless(SubTema::whereKey($data['sub_tema_id'])->where('tema_id', $data['tema_id'])->exists(), 403);

        $this->authorizeStudent((int) $data['siswa_id'], $kelas->id);
        abort_unless((int) $data['thn_ajaran'] === (int) $kelas->thn_ajaran, 403);
        $data['user_id'] = Siswa::findOrFail($data['siswa_id'])->user_id;

        DB::transaction(function () use ($data, $kelas, $user) {
            $attributes = [
                'user_id' => $data['user_id'],
                'siswa_id' => $data['siswa_id'],
                'tema_id' => $data['tema_id'],
                'sub_tema_id' => $data['sub_tema_id'],
                'thn_ajaran' => $kelas->thn_ajaran,
            ];
            $existing = Rapor::where($attributes)->first();

            if ($existing) {
                abort_unless($existing->guru_id === $user->guru->id, 403);
                $this->ensureEditable($existing);
            }

            Rapor::updateOrCreate($attributes, [
                'guru_id' => $user->guru->id,
                'keterangan' => $data['keterangan'],
                'status' => 'draft',
                'validated_by' => null,
                'validated_at' => null,
                'catatan_validasi' => null,
            ]);
        });

        return redirect()
            ->route('rapor.index', ['kelas_id' => $kelas->id])
            ->with('success', 'Catatan rapor berhasil disimpan.');
    }

    public function update(RaporRequest $request, Rapor $rapor)
    {
        $data = $request->validated();
        $user = $this->currentGuruUser();
        $rapor->load('siswa');

        $this->authorizeRaporForGuru($user, $rapor);
        $kelas = $this->authorizeKelas($user, (int) $data['kelas_id']);

        abort_unless(
            $rapor->siswa_id === (int) $data['siswa_id']
                && $rapor->tema_id === (int) $data['tema_id']
                && $rapor->sub_tema_id === (int) $data['sub_tema_id']
                && (int) $rapor->thn_ajaran === (int) $kelas->thn_ajaran,
            403,
        );

        $this->ensureEditable($rapor);

        DB::transaction(function () use ($rapor, $data, $user) {
            $rapor->update([
                'guru_id' => $user->guru->id,
                'keterangan' => $data['keterangan'],
                'status' => 'draft',
                'validated_by' => null,
                'validated_at' => null,
                'catatan_validasi' => null,
            ]);
        });

        return redirect()
            ->route('rapor.index', ['kelas_id' => $kelas->id])
            ->with('success', 'Catatan rapor berhasil diperbarui.');
    }

    public function show(Rapor $rapor)
    {
        $user = $this->currentRaporUser();
        $rapor->load(['siswa', 'tema', 'guru', 'validator']);

        abort_unless($rapor->siswa, 403);
        $this->authorizeKelas($user, $rapor->siswa->kelas_id);

        if ($user->hasRole('Guru')) {
            abort_unless($rapor->guru_id === $user->guru->id, 403);
        }

        return response()->json($rapor);
    }

    public function submit(RaporSubmitRequest $request)
    {
        $data = $request->validated();
        $user = $this->currentGuruUser();
        $kelas = $this->authorizeKelas($user, (int) $data['kelas_id']);

        abort_unless((int) $data['thn_ajaran'] === (int) $kelas->thn_ajaran, 403);

        $updated = DB::transaction(function () use ($kelas, $user) {
            return Rapor::query()
                ->where('guru_id', $user->guru->id)
                ->where('thn_ajaran', $kelas->thn_ajaran)
                ->whereIn('siswa_id', $kelas->siswa()->pluck('id'))
                ->whereIn('status', ['draft', 'ditolak'])
                ->update([
                    'status' => 'diajukan',
                    'validated_by' => null,
                    'validated_at' => null,
                    'catatan_validasi' => null,
                ]);
        });

        if ($updated === 0) {
            return redirect()
                ->route('rapor.index', ['kelas_id' => $kelas->id])
                ->with('warning', 'Tidak ada catatan rapor yang dapat diajukan.');
        }

        return redirect()
            ->route('rapor.index', ['kelas_id' => $kelas->id])
            ->with('success', 'Rapor berhasil diajukan untuk divalidasi.');
    }

    public function validationIndex()
    {
        $this->currentKepsekUser();

        $kelasId = request()->query('kelas_id');
        $tahunAjaran = request()->query('thn_ajaran');
        $kelas = Kelas::query()
            ->when($tahunAjaran, fn ($query) => $query->where('thn_ajaran', $tahunAjaran))
            ->orderByDesc('thn_ajaran')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'thn_ajaran']);
        $tahunAjaranOptions = Kelas::select('thn_ajaran')
            ->distinct()
            ->orderByDesc('thn_ajaran')
            ->pluck('thn_ajaran');
        $selectedKelas = null;
        $siswas = collect();
        $rapors = collect();

        if ($kelasId) {
            $selectedKelas = Kelas::findOrFail($kelasId);
            abort_unless(! $tahunAjaran || (int) $selectedKelas->thn_ajaran === (int) $tahunAjaran, 403);

            $siswas = Siswa::with('user:id,name,email')
                ->where('kelas_id', $selectedKelas->id)
                ->orderBy('nama')
                ->get(['id', 'user_id', 'kelas_id', 'nama', 'nis']);
            $rapors = Rapor::with([
                'siswa:id,user_id,kelas_id,nama,nis',
                'tema:id,nama_tema',
                'guru:id,nama,nip',
                'validator:id,name,email',
            ])
                ->where('thn_ajaran', $selectedKelas->thn_ajaran)
                ->whereIn('siswa_id', $siswas->pluck('id'))
                ->get();
        }

        return Inertia::render('ValidasiRapor/Index', [
            'kelas' => $kelas,
            'tahunAjaranOptions' => $tahunAjaranOptions,
            'selectedKelas' => $selectedKelas,
            'siswas' => $siswas,
            'rapors' => $rapors,
            'temaCount' => Tema::count(),
            'filters' => [
                'kelas_id' => $kelasId,
                'thn_ajaran' => $tahunAjaran,
            ],
        ]);
    }

    public function approve(RaporApprovalRequest $request, Siswa $siswa)
    {
        $data = $request->validated();
        $user = $this->currentKepsekUser();
        $kelas = $this->validationKelas($data);

        abort_unless($siswa->kelas_id === $kelas->id, 403);

        $rapors = $this->raporsForStudent($siswa, $kelas->thn_ajaran);
        $this->ensureReadyForApproval($rapors);

        DB::transaction(function () use ($rapors, $user) {
            $rapors->each->update([
                'status' => 'disetujui',
                'validated_by' => $user->id,
                'validated_at' => now(),
                'catatan_validasi' => null,
            ]);
        });

        return redirect()
            ->route('rapor.validation.index', ['kelas_id' => $kelas->id, 'thn_ajaran' => $kelas->thn_ajaran])
            ->with('success', 'Rapor siswa berhasil disetujui.');
    }

    public function reject(RaporRejectRequest $request, Siswa $siswa)
    {
        $data = $request->validated();
        $user = $this->currentKepsekUser();
        $kelas = $this->validationKelas($data);

        abort_unless($siswa->kelas_id === $kelas->id, 403);

        $rapors = $this->raporsForStudent($siswa, $kelas->thn_ajaran);

        if ($rapors->isEmpty()) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Rapor siswa belum tersedia.',
            ]);
        }

        if (! $rapors->every(fn (Rapor $rapor) => $rapor->status === 'diajukan')) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Rapor siswa harus berstatus diajukan sebelum ditolak.',
            ]);
        }

        DB::transaction(function () use ($rapors, $data, $user) {
            $rapors->each->update([
                'status' => 'ditolak',
                'validated_by' => $user->id,
                'validated_at' => now(),
                'catatan_validasi' => $data['catatan_validasi'],
            ]);
        });

        return redirect()
            ->route('rapor.validation.index', ['kelas_id' => $kelas->id, 'thn_ajaran' => $kelas->thn_ajaran])
            ->with('success', 'Rapor siswa berhasil ditolak.');
    }

    public function approveAll(RaporApprovalRequest $request)
    {
        $data = $request->validated();
        $user = $this->currentKepsekUser();
        $kelas = $this->validationKelas($data);
        $siswas = Siswa::where('kelas_id', $kelas->id)->get(['id']);
        $rapors = Rapor::where('thn_ajaran', $kelas->thn_ajaran)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get();
        $submittedSiswaIds = $rapors->where('status', 'diajukan')->pluck('siswa_id')->unique();

        if ($submittedSiswaIds->isEmpty()) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Tidak ada rapor berstatus diajukan untuk disetujui.',
            ]);
        }

        $submittedSiswaIds->each(function (int $siswaId) use ($rapors) {
            $this->ensureReadyForApproval($rapors->where('siswa_id', $siswaId));
        });

        DB::transaction(function () use ($submittedSiswaIds, $kelas, $user) {
            Rapor::where('thn_ajaran', $kelas->thn_ajaran)
                ->whereIn('siswa_id', $submittedSiswaIds)
                ->where('status', 'diajukan')
                ->update([
                    'status' => 'disetujui',
                    'validated_by' => $user->id,
                    'validated_at' => now(),
                    'catatan_validasi' => null,
                ]);
        });

        return redirect()
            ->route('rapor.validation.index', ['kelas_id' => $kelas->id, 'thn_ajaran' => $kelas->thn_ajaran])
            ->with('success', 'Seluruh rapor yang diajukan berhasil disetujui.');
    }

    private function kelasForUser(User $user)
    {
        return Kelas::query()
            ->active()
            ->when($user->hasRole('Guru'), function ($query) use ($user) {
                $query->whereHas('jadwal', function ($query) use ($user) {
                    $query->where('guru_id', $user->guru->id);
                });
            })
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'thn_ajaran']);
    }

    private function authorizeKelas(User $user, int $kelasId): Kelas
    {
        $kelas = Kelas::findOrFail($kelasId);

        if ($user->hasRole('Guru')) {
            abort_unless($kelas->jadwal()->where('guru_id', $user->guru->id)->exists(), 403);
        }

        return $kelas;
    }

    private function authorizeStudent(int $siswaId, int $kelasId): void
    {
        abort_unless(
            Siswa::where('id', $siswaId)->where('kelas_id', $kelasId)->exists(),
            403,
        );
    }

    private function authorizeRaporForGuru(User $user, Rapor $rapor): void
    {
        abort_unless($rapor->siswa, 403);
        $this->authorizeKelas($user, $rapor->siswa->kelas_id);
        abort_unless($rapor->guru_id === $user->guru->id, 403);
    }

    private function ensureEditable(Rapor $rapor): void
    {
        abort_unless(in_array($rapor->status, ['draft', 'ditolak'], true), 403);
    }

    private function validationKelas(array $data): Kelas
    {
        $kelas = Kelas::findOrFail($data['kelas_id']);

        abort_unless((int) $kelas->thn_ajaran === (int) $data['thn_ajaran'], 403);

        return $kelas;
    }

    private function raporsForStudent(Siswa $siswa, string|int $thnAjaran)
    {
        return Rapor::where('siswa_id', $siswa->id)
            ->where('thn_ajaran', $thnAjaran)
            ->get();
    }

    private function ensureReadyForApproval($rapors): void
    {
        $temaCount = Tema::count();
        $hasCompleteNotes = $rapors->isNotEmpty()
            && $rapors->pluck('tema_id')->unique()->count() === $temaCount
            && $rapors->every(fn (Rapor $rapor) => filled($rapor->keterangan));

        if (! $hasCompleteNotes) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Masih terdapat tema yang belum memiliki catatan penilaian.',
            ]);
        }

        if (! $rapors->every(fn (Rapor $rapor) => $rapor->status === 'diajukan')) {
            throw ValidationException::withMessages([
                'kelas_id' => 'Rapor harus berstatus diajukan sebelum disetujui.',
            ]);
        }
    }

    private function currentRaporUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing(['role', 'guru']);

        abort_unless(
            $user->hasRole('Admin')
                || $user->hasRole('Staff Akademik')
                || $user->hasRole('Kepsek')
                || ($user->hasRole('Guru') && $user->guru),
            403,
        );

        return $user;
    }

    private function currentGuruUser(): User
    {
        $user = $this->currentRaporUser();

        abort_unless($user->hasRole('Guru') && $user->guru, 403);

        return $user;
    }

    private function currentKepsekUser(): User
    {
        $user = $this->currentRaporUser();

        abort_unless($user->hasRole('Kepsek'), 403);

        return $user;
    }
}
