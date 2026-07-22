<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_komponen_penilaians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komponen');
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('nama_komponen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_komponen_penilaians');
    }
};
