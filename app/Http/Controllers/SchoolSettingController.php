<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolSettingRequest;
use App\Models\SchoolSetting;
use App\Services\ProfilePhotoService;
use Inertia\Inertia;

class SchoolSettingController extends Controller
{
    public function edit()
    {
        $this->authorizeAdmin();

        return Inertia::render('SchoolSettings/Edit', [
            'setting' => SchoolSetting::current(),
        ]);
    }

    public function update(SchoolSettingRequest $request, ProfilePhotoService $profilePhotoService)
    {
        $setting = SchoolSetting::current();
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $profilePhotoService->replace(
                $request->file('logo'),
                $setting->logo,
            );
        }

        $setting->update($data);

        return redirect()
            ->route('school-settings.edit')
            ->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        $user?->loadMissing('role');

        abort_unless($user?->hasRole('Admin'), 403);
    }
}
