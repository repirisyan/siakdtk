<?php

namespace App\Models;

use Database\Factories\KontenFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konten extends Model
{
    /** @use HasFactory<KontenFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'jenis_konten', 'judul', 'slug', 'ringkasan', 'konten', 'thumbnail',
        'tanggal_publish', 'status', 'tanggal_event', 'jam_mulai', 'jam_selesai', 'lokasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_publish' => 'datetime',
            'tanggal_event' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function galeris(): HasMany
    {
        return $this->hasMany(KontenGaleri::class)->orderBy('urutan');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('tanggal_publish')->orWhere('tanggal_publish', '<=', now());
            });
    }
}
