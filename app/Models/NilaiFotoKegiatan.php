<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NilaiFotoKegiatan extends Model
{
    use HasFactory;

    protected $fillable = ['nilai_id', 'path'];

    protected $appends = ['url'];

    public function nilai(): BelongsTo
    {
        return $this->belongsTo(Nilai::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
