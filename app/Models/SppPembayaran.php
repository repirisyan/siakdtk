<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppPembayaran extends Model
{
    use HasFactory;

    protected $table = 'detail_pembayarans';

    protected $fillable = [
        'pembayaran_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
        'bukti_pembayaran',
        'keterangan',
        'received_by',
        'status_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'date',
            'jumlah_bayar' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function spp(): BelongsTo
    {
        return $this->belongsTo(Spp::class, 'pembayaran_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
