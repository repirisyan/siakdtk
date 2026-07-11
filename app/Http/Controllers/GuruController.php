<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuruRequest;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class GuruController extends Controller
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

        $guruSorts = ['user_name', 'user_email', 'user_status'];

        $gurus = Guru::with('user:id,name,email,status')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nuptk', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), function ($query) use ($status) {
                $query->whereHas('user', fn ($query) => $query->where('status', $status === 'aktif'));
            })
            ->when($sort === 'user_name', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'gurus.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'user_email', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('email')->whereColumn('users.id', 'gurus.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'user_status', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('status')->whereColumn('users.id', 'gurus.user_id'),
                    $direction,
                );
            })
            ->when(! in_array($sort, $guruSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Guru/Index', [
            'gurus' => $gurus,
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
        return Inertia::render('Guru/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GuruRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $roleGuru = Role::where('role_name', 'Guru')->firstOrFail();

            $user = User::create([
                'role_id' => $roleGuru->id,
                'name' => $data['name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['password']),
                'status' => true,
                'email_verified_at' => now(),
            ]);

            Guru::create([
                ...Arr::except($data, ['name', 'user_email', 'password', 'password_confirmation']),
                'user_id' => $user->id,
            ]);

            return $user;
        });

        $user->notify(new AccountCreatedNotification);

        return redirect()
            ->route('guru.index')
            ->with('success', 'Guru berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Guru $guru)
    {
        return Inertia::render('Guru/Show', [
            'guru' => $guru->load('user:id,name,email'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guru $guru)
    {
        return Inertia::render('Guru/Edit', [
            'guru' => $guru->load('user:id,name,email'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GuruRequest $request, Guru $guru)
    {
        $data = $request->validated();
        $guru->load('user');

        DB::transaction(function () use ($data, $guru) {
            $userData = Arr::only($data, ['name', 'user_email']);
            $userData['email'] = $userData['user_email'];
            unset($userData['user_email']);

            if ($data['password']) {
                $userData['password'] = Hash::make($data['password']);
            }

            $guru->user->update($userData);
            $guru->update(Arr::except($data, ['name', 'user_email', 'password', 'password_confirmation']));
        });

        return redirect()
            ->route('guru.index')
            ->with('success', 'Guru berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guru $guru)
    {
        $guru->delete();

        return redirect()
            ->route('guru.index')
            ->with('success', 'Guru berhasil dihapus.');
    }

    public function toggleStatus(Guru $guru)
    {
        $guru->loadMissing('user');

        if (! $guru->user) {
            return redirect()
                ->route('guru.index')
                ->with('error', 'Akun pengguna guru tidak ditemukan.');
        }

        $status = ! $guru->user->status;
        $guru->user->update(['status' => $status]);

        return redirect()
            ->route('guru.index')
            ->with('success', $status ? 'Guru berhasil diaktifkan.' : 'Guru berhasil dinonaktifkan.');
    }
}
