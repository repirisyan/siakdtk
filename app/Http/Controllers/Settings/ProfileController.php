<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfilePhotoRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\ProfilePhotoService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, ProfilePhotoService $profilePhotoService): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        unset($data['foto_profil']);

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('foto_profil')) {
            $user->foto_profil = $profilePhotoService->replace(
                $request->file('foto_profil'),
                $user->foto_profil,
            );
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data berhasil diperbarui.']);

        return to_route('profile.edit');
    }

    public function destroyPhoto(Request $request, ProfilePhotoService $profilePhotoService): RedirectResponse
    {
        $user = $request->user();

        $profilePhotoService->delete($user->foto_profil);
        $user->update(['foto_profil' => null]);

        return to_route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }

    public function updatePhoto(ProfilePhotoRequest $request, ProfilePhotoService $profilePhotoService): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'foto_profil' => $profilePhotoService->replace(
                $request->file('foto_profil'),
                $user->foto_profil,
            ),
        ]);

        return to_route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        app(ProfilePhotoService::class)->delete($user->foto_profil);

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
