<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor_akhirs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Siswa::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Kelas::class)->constrained('kelas')->restrictOnDelete();
            $table->year('thn_ajaran');
            $table->enum('status', ['draft', 'menunggu_validasi', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignIdFor(User::class, 'approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignIdFor(User::class, 'rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'kelas_id', 'thn_ajaran']);
        });

        Schema::create('rapor_akhir_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapor_akhir_id')->constrained('rapor_akhirs')->cascadeOnDelete();
            $table->foreignIdFor(Tema::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Guru::class)->constrained()->restrictOnDelete();
            $table->text('keterangan');
            $table->timestamps();
            $table->unique(['rapor_akhir_id', 'tema_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor_akhir_details');
        Schema::dropIfExists('rapor_akhirs');
    }
};
