<?php

use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Siswa::class, 'siswa_id')->constrained();
            $table->foreignIdFor(Jadwal::class, 'jadwal_id')->constrained();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absens');
    }
};
