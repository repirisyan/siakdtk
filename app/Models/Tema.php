<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tema extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function rapor(): HasMany
    {
        return $this->hasMany(Rapor::class, 'tema_id');
    }
}
