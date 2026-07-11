<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapor extends Model
{
    protected $fillable = [
        'user_id',
        'siswa_id',
        'guru_id',
        'tema_id',
        'keterangan',
        'status',
        'validated_by',
        'validated_at',
        'catatan_validasi',
        'thn_ajaran',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class, 'tema_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
