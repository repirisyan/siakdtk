<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nilai extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function absen(): BelongsTo
    {
        return $this->belongsTo(Absen::class, 'absen_id');
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absen::class, 'absen_id');
    }

    public function komponenPenilaian(): BelongsTo
    {
        return $this->belongsTo(KomponenPenilaian::class);
    }

    public function fotoKegiatans(): HasMany
    {
        return $this->hasMany(NilaiFotoKegiatan::class);
    }
}
