<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidtransTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['spp_id', 'user_id', 'order_id', 'transaction_id', 'snap_token', 'gross_amount', 'payment_type', 'transaction_status', 'response_payload', 'paid_at'];

    protected function casts(): array
    {
        return ['gross_amount' => 'decimal:2', 'response_payload' => 'array', 'paid_at' => 'datetime'];
    }

    public function spp(): BelongsTo
    {
        return $this->belongsTo(Spp::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
