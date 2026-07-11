<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use App\Services\ProfilePhotoService;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $userSorts = ['id', 'name', 'email', 'status', 'created_at'];

        $users = User::with([
            'role:id,role_name',
            'guru:id,user_id,nama,nip',
            'siswa:id,user_id,nama,nis',
        ])
            ->where('role_id', '!=', '4')->where('role_id', '!=', '6')
            ->withCount('siswas')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('role', function ($query) use ($search) {
                            $query->where('role_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sort === 'role_name', function ($query) use ($direction) {
                $query->orderBy(
                    Role::select('role_name')->whereColumn('roles.id', 'users.role_id'),
                    $direction,
                );
            })
            ->when(in_array($sort, $userSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            }, function ($query) use ($direction) {
                $query->orderBy('id', $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
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
        $this->authorizeAdmin();

        return Inertia::render('User/Create', [
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request, ProfilePhotoService $profilePhotoService)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
            'email_verified_at' => now(),
            'foto_profil' => $request->hasFile('foto_profil')
                ? $profilePhotoService->replace($request->file('foto_profil'))
                : null,
        ]);

        $user->notify(new AccountCreatedNotification);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorizeAdmin();

        return Inertia::render('User/Show', [
            'user' => $user->load([
                'role:id,role_name',
                'guru:id,user_id,nama,nip,email,nohp_guru',
                'siswa:id,user_id,kelas_id,nama,nis,nisn',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorizeAdmin();

        return Inertia::render('User/Edit', [
            'user' => $user->load('role:id,role_name'),
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user, ProfilePhotoService $profilePhotoService)
    {
        $data = $request->validated();
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
            'email_verified_at' => now(),
        ];

        if ($data['password']) {
            $userData['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('foto_profil')) {
            $userData['foto_profil'] = $profilePhotoService->replace(
                $request->file('foto_profil'),
                $user->foto_profil,
            );
        }

        $user->update($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, ProfilePhotoService $profilePhotoService)
    {
        $this->authorizeAdmin();
        $user->loadMissing(['guru'])->loadCount('siswas');

        if ($user->guru || $user->siswas_count > 0) {
            return redirect()
                ->route('users.index')
                ->with('error', 'User masih digunakan oleh data lain.');
        }

        $profilePhotoService->delete($user->foto_profil);
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function toggleStatus(User $user)
    {
        $this->authorizeAdmin();

        $user->update([
            'status' => ! $user->status,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', $user->status ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    public function destroyPhoto(User $user, ProfilePhotoService $profilePhotoService)
    {
        $this->authorizeAdmin();

        $profilePhotoService->delete($user->foto_profil);
        $user->update(['foto_profil' => null]);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Foto profil user berhasil dihapus.');
    }

    public function verifyEmail(User $user)
    {
        $this->authorizeAdmin();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('users.index')->with('warning', 'Email user sudah terverifikasi.');
        }

        $user->markEmailAsVerified();

        return redirect()->route('users.index')->with('success', 'Email user berhasil diverifikasi.');
    }

    public function resendVerificationEmail(User $user)
    {
        $this->authorizeAdmin();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('users.index')->with('warning', 'Email user sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('users.index')->with('success', 'Email verifikasi sedang diproses dan akan segera dikirim.');
    }

    private function roles()
    {
        return Role::orderBy('role_name')->where('role_name', '!=', 'Guru')->where('role_name', '!=', 'Orangtua Siswa')->get(['id', 'role_name']);
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('role');

        abort_unless($user->hasRole('Admin'), 403);
    }
}
