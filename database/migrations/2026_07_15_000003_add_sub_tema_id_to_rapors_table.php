<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            $table->foreignId('sub_tema_id')->nullable()->after('tema_id')->constrained('sub_temas')->nullOnDelete();
            $table->index(['siswa_id', 'sub_tema_id', 'thn_ajaran'], 'rapors_siswa_sub_tema_tahun_index');
        });
    }

    public function down(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            $table->dropIndex('rapors_siswa_sub_tema_tahun_index');
            $table->dropConstrainedForeignId('sub_tema_id');
        });
    }
};
