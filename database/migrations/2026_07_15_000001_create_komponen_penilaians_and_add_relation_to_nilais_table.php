<?php

use App\Models\KomponenPenilaian;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_tema_id')->constrained('sub_temas')->cascadeOnDelete();
            $table->string('nama_komponen');
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['sub_tema_id', 'nama_komponen']);
        });

        Schema::table('nilais', function (Blueprint $table) {
            $table->foreignIdFor(KomponenPenilaian::class, 'komponen_penilaian_id')
                ->nullable()
                ->after('absen_id')
                ->constrained('komponen_penilaians')
                ->nullOnDelete();
            $table->unique(['absen_id', 'komponen_penilaian_id']);
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropUnique(['absen_id', 'komponen_penilaian_id']);
            $table->dropConstrainedForeignId('komponen_penilaian_id');
        });
        Schema::dropIfExists('komponen_penilaians');
    }
};
