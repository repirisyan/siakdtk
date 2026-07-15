<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spp extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'siswa_id',
        'thn_ajaran',
        'jenis_pembayaran',
        'jenis_pembayaran_id',
        'tanggal_tagihan',
        'jatuh_tempo',
        'nominal',
        'keterangan',
        'last_notification_at',
        'last_notified_by',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_tagihan' => 'date',
            'jatuh_tempo' => 'date',
            'last_notification_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jenisPembayaran(): BelongsTo
    {
        return $this->belongsTo(JenisPembayaran::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SppPembayaran::class, 'pembayaran_id');
    }

    public function approvedPayments(): HasMany
    {
        return $this->hasMany(SppPembayaran::class, 'pembayaran_id')->where('status_verifikasi', 'approved');
    }

    public function notifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_notified_by');
    }
}
