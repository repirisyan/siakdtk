<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('SIAKDTK');
            $table->string('logo')->nullable();
            $table->string('alamat', 1000)->nullable();
            $table->string('nomor_telepon', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('tentang')->nullable();
            $table->text('sejarah_singkat')->nullable();
            $table->string('tagline')->nullable();
            $table->boolean('pendaftaran_dibuka')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
