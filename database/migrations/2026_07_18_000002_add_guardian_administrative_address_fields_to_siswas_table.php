<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('desa')->nullable()->after('alamat');
            $table->string('kecamatan')->nullable()->after('desa');
            $table->string('kabupaten')->nullable()->after('kecamatan');
            $table->string('provinsi')->nullable()->after('kabupaten');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'desa',
                'kecamatan',
                'kabupaten',
                'provinsi',
            ]);
        });
    }
};
