<?php

namespace App\Models;

use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['role_id', 'name', 'email', 'email_verified_at', 'password', 'status', 'foto_profil'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $appends = ['avatar'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function kontens(): HasMany
    {
        return $this->hasMany(Konten::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->role_name === $role;
    }

    public function sendEmailVerificationNotification(): void
    {
        if (app()->environment('testing')) {
            $this->notify(new VerifyEmail);

            return;
        }

        $this->notify(new QueuedVerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        if (app()->environment('testing')) {
            $this->notify(new ResetPassword($token));

            return;
        }

        $this->notify(new QueuedResetPassword($token));
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->foto_profil
            ? Storage::disk('public')->url($this->foto_profil)
            : null;
    }
}
