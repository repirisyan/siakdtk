<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('desa_wali')->nullable()->after('alamat_wali');
            $table->string('kecamatan_wali')->nullable()->after('desa_wali');
            $table->string('kabupaten_wali')->nullable()->after('kecamatan_wali');
            $table->string('provinsi_wali')->nullable()->after('kabupaten_wali');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'desa_wali',
                'kecamatan_wali',
                'kabupaten_wali',
                'provinsi_wali',
            ]);
        });
    }
};
