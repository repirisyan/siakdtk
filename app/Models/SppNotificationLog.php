<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppNotificationLog extends Model
{
    use HasFactory;

    protected $fillable = ['sent_by', 'batch_id', 'recipient_count', 'source', 'filters', 'sent_at'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'sent_at' => 'datetime'];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
