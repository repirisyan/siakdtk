<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temas', function (Blueprint $table) {
            $table->year('thn_ajaran')->nullable()->after('nama_tema')->index();
        });
        Schema::create('sub_temas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_id')->constrained('temas')->cascadeOnDelete();
            $table->string('nama_sub_tema');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
        DB::table('temas')->orderBy('id')->each(function (object $tema): void {
            $tahun = DB::table('jadwals')->join('kelas', 'kelas.id', '=', 'jadwals.kelas_id')->where('jadwals.tema_id', $tema->id)->value('kelas.thn_ajaran');
            DB::table('temas')->where('id', $tema->id)->update(['thn_ajaran' => $tahun ?: now()->year]);
        });

        DB::table('temas')->update(['status' => false]);
        $latestId = DB::table('temas')->max('id');
        if ($latestId) {
            DB::table('temas')->where('id', $latestId)->update(['status' => true]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_temas');
        Schema::table('temas', function (Blueprint $table) {
            $table->dropIndex(['thn_ajaran']);
            $table->dropColumn('thn_ajaran');
        });
    }
};
