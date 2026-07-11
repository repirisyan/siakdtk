<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('thn_ajaran')->index();
        });

        Schema::table('temas', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('nama_tema')->index();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });

        Schema::table('temas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
