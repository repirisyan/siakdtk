<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_sekolah',
        'logo',
        'alamat',
        'nomor_telepon',
        'email',
        'website',
        'visi',
        'misi',
        'tentang',
        'sejarah_singkat',
        'tagline',
        'pendaftaran_dibuka',
    ];

    protected function casts(): array
    {
        return ['pendaftaran_dibuka' => 'boolean'];
    }

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'nama_sekolah' => config('app.name', 'SIAKDTK'),
            'tagline' => 'Sistem Informasi Akademik TK',
            'pendaftaran_dibuka' => true,
        ]);
    }
}
