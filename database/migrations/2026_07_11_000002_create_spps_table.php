<?php

use App\Models\Siswa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Siswa::class, 'siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->year('thn_ajaran');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'thn_ajaran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
