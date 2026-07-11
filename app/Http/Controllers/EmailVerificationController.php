<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()
            ->route('login')
            ->with('success', 'Email berhasil diverifikasi. Menunggu aktivasi akun oleh pihak sekolah.');
    }

    public function resend(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Jika email terdaftar dan belum terverifikasi, email sedang diproses dan akan segera dikirim.');
    }
}
