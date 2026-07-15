<?php

use App\Models\Siswa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            $table->foreignIdFor(Siswa::class, 'siswa_id')
                ->nullable()
                ->after('user_id')
                ->constrained('siswas')
                ->nullOnDelete();
        });

        Siswa::query()->select(['id', 'user_id'])->orderBy('id')->each(function (Siswa $siswa): void {
            // Data lama sebelumnya hanya mendukung satu siswa per akun.
            // Mengisi rapor yang belum memiliki siswa dengan siswa pertama milik akun tersebut.
            DB::table('rapors')
                ->where('user_id', $siswa->user_id)
                ->whereNull('siswa_id')
                ->update(['siswa_id' => $siswa->id]);
        });

        Schema::table('rapors', function (Blueprint $table) {
            $table->index(['siswa_id', 'tema_id', 'thn_ajaran'], 'rapors_siswa_tema_tahun_index');
        });
    }

    public function down(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            $table->dropIndex('rapors_siswa_tema_tahun_index');
            $table->dropConstrainedForeignId('siswa_id');
        });
    }
};
