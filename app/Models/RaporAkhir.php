<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaporAkhir extends Model
{
    protected $fillable = [
        'siswa_id', 'kelas_id', 'thn_ajaran', 'status', 'approved_by',
        'approved_at', 'rejected_by', 'rejected_at', 'catatan_penolakan',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'rejected_at' => 'datetime'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(RaporAkhirDetail::class);
    }
}
