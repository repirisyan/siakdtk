<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->enum('jenis_konten', ['berita', 'event', 'pengumuman', 'galeri']);
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('konten')->nullable();
            $table->string('thumbnail')->nullable();
            $table->dateTime('tanggal_publish')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->date('tanggal_event')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontens');
    }
};
