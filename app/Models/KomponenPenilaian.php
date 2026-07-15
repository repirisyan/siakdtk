<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KomponenPenilaian extends Model
{
    use HasFactory;

    protected $fillable = ['sub_tema_id', 'nama_komponen', 'deskripsi', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function subTema(): BelongsTo
    {
        return $this->belongsTo(SubTema::class);
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }
}
