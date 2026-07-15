<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPembayaran extends Model
{
    use HasFactory;

    protected $fillable = ['nama_jenis', 'deskripsi', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function spps(): HasMany
    {
        return $this->hasMany(Spp::class);
    }
}
