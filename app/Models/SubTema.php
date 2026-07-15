<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubTema extends Model
{
    use HasFactory;

    protected $fillable = ['tema_id', 'nama_sub_tema', 'deskripsi'];

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function komponenPenilaians(): HasMany
    {
        return $this->hasMany(KomponenPenilaian::class);
    }
}
