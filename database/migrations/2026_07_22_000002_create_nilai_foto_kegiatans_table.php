<?php

use App\Models\Nilai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_foto_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Nilai::class, 'nilai_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_foto_kegiatans');
    }
};
