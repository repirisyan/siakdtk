<?php

use App\Models\TahunAjaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')
                ->nullable()
                ->after('id')
                ->constrained('tahun_ajarans')
                ->restrictOnDelete();
        });

        DB::table('kelas')->select('thn_ajaran')->distinct()->orderBy('thn_ajaran')->each(function (object $kelas): void {
            $tahunAjaran = TahunAjaran::firstOrCreate([
                'tahun_ajaran' => $kelas->thn_ajaran,
            ]);

            DB::table('kelas')
                ->where('thn_ajaran', $kelas->thn_ajaran)
                ->update(['tahun_ajaran_id' => $tahunAjaran->id]);
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tahun_ajaran_id');
        });
    }
};
