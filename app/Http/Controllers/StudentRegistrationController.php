<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRegistrationRequest;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\StudentRegistrationNotification;
use App\Services\ProfilePhotoService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentRegistrationController extends Controller
{
    public function create()
    {
        return $this->registrationPage(false);
    }

    public function store(StudentRegistrationRequest $request, ProfilePhotoService $profilePhotoService)
    {
        $this->ensureRegistrationOpen();
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $request, $profilePhotoService) {
            $roleOrangtua = Role::where('role_name', 'Orangtua Siswa')->firstOrFail();

            $user = User::create([
                'role_id' => $roleOrangtua->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => false,
                'email_verified_at' => null,
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
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $user;
        });

        $user->sendEmailVerificationNotification();
        $user->notify(new StudentRegistrationNotification($data['nama'], 'submitted'));

        return redirect()
            ->route('register.success')
            ->with('success', 'Pendaftaran berhasil. Email verifikasi sedang diproses dan akan segera dikirim.');
    }

    public function success()
    {
        return Inertia::render('auth/RegistrationSuccess');
    }

    public function createAdditional()
    {
        $this->currentParentUser();

        return $this->registrationPage(true);
    }

    public function storeAdditional(StudentRegistrationRequest $request, ProfilePhotoService $profilePhotoService)
    {
        $this->ensureRegistrationOpen();
        $parent = $this->currentParentUser();
        $data = $request->validated();

        DB::transaction(function () use ($data, $parent): void {
            Siswa::create([
                ...Arr::except($data, [
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                    'thn_ajaran',
                    'foto_profil',
                ]),
                'user_id' => $parent->id,
                'tanggal_registrasi' => today(),
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Pendaftaran anak berhasil dikirim dan menunggu verifikasi sekolah.');
    }

    private function registrationPage(bool $existingParent)
    {
        $setting = SchoolSetting::current();

        return Inertia::render('auth/Register', [
            'registrationOpen' => $setting->pendaftaran_dibuka,
            'school' => $setting->only(['nama_sekolah', 'tagline', 'logo_url']),
            'existingParent' => $existingParent,
        ]);
    }

    private function ensureRegistrationOpen(): void
    {
        abort_unless(SchoolSetting::current()->pendaftaran_dibuka, 403, 'Pendaftaran siswa saat ini sedang ditutup oleh pihak sekolah.');
    }

    private function currentParentUser(): User
    {
        $user = auth()->user();
        $user?->loadMissing('role');

        abort_unless($user instanceof User && $user->hasRole('Orangtua Siswa'), 403);

        return $user;
    }
}
