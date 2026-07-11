<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KontenGaleri extends Model
{
    use HasFactory;

    protected $fillable = ['konten_id', 'gambar', 'caption', 'urutan'];

    public function konten(): BelongsTo
    {
        return $this->belongsTo(Konten::class);
    }
}
