<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveSiswaRequest;
use App\Http\Requests\MoveSiswaClassRequest;
use App\Http\Requests\SiswaRequest;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use App\Notifications\StudentRegistrationNotification;
use App\Services\ProfilePhotoService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->currentSiswaManager();

        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $status = request()->query('status');

        $relationSorts = ['user_name', 'email', 'nama_kelas', 'thn_ajaran'];

        $siswas = Siswa::with([
            'user:id,name,email,status,foto_profil',
            'kelas:id,nama_kelas,thn_ajaran',
            'approver:id,name,email',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('kelas', function ($query) use ($search) {
                            $query
                                ->where('nama_kelas', 'like', "%{$search}%")
                                ->orWhere('thn_ajaran', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, ['pending', 'aktif', 'ditolak'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($sort === 'user_name', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'siswas.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'email', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('email')->whereColumn('users.id', 'siswas.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'nama_kelas', function ($query) use ($direction) {
                $query->orderBy(
                    Kelas::select('nama_kelas')->whereColumn('kelas.id', 'siswas.kelas_id'),
                    $direction,
                );
            })
            ->when($sort === 'thn_ajaran', function ($query) use ($direction) {
                $query->orderBy(
                    Kelas::select('thn_ajaran')->whereColumn('kelas.id', 'siswas.kelas_id'),
                    $direction,
                );
            })
            ->when(! in_array($sort, $relationSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Siswa/Index', [
            'siswas' => $siswas,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'status' => $status,
            ],
            'kelasOptions' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran']),
            'canMoveClass' => auth()->user()?->loadMissing('role')->hasRole('Admin') ?? false,
            'canApproveSiswa' => true,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->currentSiswaManager();

        $tahun = request()->query('thn_ajaran');

        return Inertia::render('Siswa/Create', [
            'tahunAjarans' => Kelas::active()->select('thn_ajaran')
                ->distinct()
                ->orderByDesc('thn_ajaran')
                ->get(),
            'kelas' => $tahun
                ? Kelas::active()->where('thn_ajaran', $tahun)
                    ->orderBy('nama_kelas')
                    ->get(['id', 'nama_kelas', 'thn_ajaran'])
                : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SiswaRequest $request, ProfilePhotoService $profilePhotoService)
    {
        $data = $request->validated();
        $manager = $this->currentSiswaManager();

        $user = DB::transaction(function () use ($data, $manager, $request, $profilePhotoService) {
            $roleOrangtua = Role::where('role_name', 'Orangtua Siswa')->firstOrFail();

            $user = User::create([
                'role_id' => $roleOrangtua->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => true,
                'email_verified_at' => now(),
                'foto_profil' => $request->hasFile('foto_profil')
                    ? $profilePhotoService->replace($request->file('foto_profil'))
                    : null,
            ]);

            Siswa::create([
                ...Arr::except($data, [
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                    'thn_ajaran',
                    'foto_profil',
                ]),
                'user_id' => $user->id,
                'tanggal_registrasi' => today(),
                'status' => 'aktif',
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);

            return $user;
        });

        $user->notify(new AccountCreatedNotification);

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $this->currentSiswaManager();

        return Inertia::render('Siswa/Show', [
            'siswa' => $siswa->load([
                'user:id,name,email,status,foto_profil',
                'kelas:id,nama_kelas,thn_ajaran',
                'approver:id,name,email',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $this->currentSiswaManager();

        $siswa->load(['user:id,name,email,status,foto_profil', 'kelas:id,nama_kelas,thn_ajaran']);
        $tahun = request()->query('thn_ajaran', $siswa->kelas->thn_ajaran);

        return Inertia::render('Siswa/Edit', [
            'siswa' => $siswa,
            'tahunAjarans' => Kelas::active()->select('thn_ajaran')
                ->distinct()
                ->orderByDesc('thn_ajaran')
                ->get(),
            'kelas' => Kelas::active()->where('thn_ajaran', $tahun)
                ->orderBy('nama_kelas')
                ->get(['id', 'nama_kelas', 'thn_ajaran']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SiswaRequest $request, Siswa $siswa, ProfilePhotoService $profilePhotoService)
    {
        $data = $request->validated();
        $this->currentSiswaManager();
        $siswa->load('user');

        DB::transaction(function () use ($data, $siswa, $request, $profilePhotoService) {
            $userData = Arr::only($data, ['name', 'email']);

            if ($data['password']) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($request->hasFile('foto_profil')) {
                $userData['foto_profil'] = $profilePhotoService->replace(
                    $request->file('foto_profil'),
                    $siswa->user->foto_profil,
                );
            }

            $siswa->user->update($userData);
            $siswa->update(Arr::except($data, [
                'name',
                'email',
                'password',
                'password_confirmation',
                'thn_ajaran',
                'foto_profil',
            ]));
        });

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $this->currentSiswaManager();

        $siswa->delete();

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    public function moveClass(MoveSiswaClassRequest $request, Siswa $siswa)
    {
        $data = $request->validated();

        DB::transaction(function () use ($siswa, $data) {
            $siswa->update([
                'kelas_id' => $data['kelas_id'],
            ]);
        });

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dipindahkan ke kelas baru.');
    }

    public function approve(ApproveSiswaRequest $request, Siswa $siswa)
    {
        $manager = $this->currentSiswaManager();

        abort_unless(in_array($siswa->status, ['pending', 'ditolak'], true), 403);

        $data = $request->validated();

        DB::transaction(function () use ($siswa, $manager, $data) {
            $siswa->update([
                'status' => 'aktif',
                'kelas_id' => $data['kelas_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'],
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);
            $siswa->user()->update(['status' => true]);
        });

        $siswa->loadMissing('user');
        $siswa->user?->notify(new StudentRegistrationNotification($siswa->nama, 'approved'));

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Pendaftaran siswa berhasil disetujui.');
    }

    public function reject(Siswa $siswa)
    {
        $manager = $this->currentSiswaManager();

        abort_unless(in_array($siswa->status, ['pending', 'aktif'], true), 403);

        DB::transaction(function () use ($siswa, $manager) {
            $siswa->update([
                'status' => 'ditolak',
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);

            $siswa->loadMissing('user.siswas');

            if (! $siswa->user?->siswas->contains('status', 'aktif')) {
                $siswa->user?->update(['status' => false]);
            }
        });

        $siswa->loadMissing('user');
        $siswa->user?->notify(new StudentRegistrationNotification($siswa->nama, 'rejected'));

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Pendaftaran siswa berhasil ditolak.');
    }

    private function currentSiswaManager(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('role');

        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Akademik'), 403);

        return $user;
    }
}
