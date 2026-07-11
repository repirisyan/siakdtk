<?php

use App\Models\Konten;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konten_galeris', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Konten::class)->constrained()->cascadeOnDelete();
            $table->string('gambar');
            $table->string('caption')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konten_galeris');
    }
};
