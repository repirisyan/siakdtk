<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaporAkhirDetail extends Model
{
    use HasFactory;

    protected $fillable = ['rapor_akhir_id', 'tema_id', 'guru_id', 'keterangan'];

    public function raporAkhir(): BelongsTo
    {
        return $this->belongsTo(RaporAkhir::class);
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
