<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nohp_wali', 15)->nullable()->after('nama_wali');
            $table->date('ttl_wali')->nullable()->after('nohp_wali');
            $table->string('agama_wali', 15)->nullable()->after('ttl_wali');
            $table->string('pekerjaan_wali')->nullable()->after('agama_wali');
            $table->string('penghasilan_wali')->nullable()->after('pekerjaan_wali');
            $table->string('alamat_wali', 1000)->nullable()->after('penghasilan_wali');
            $table->string('akta_kelahiran_file')->nullable()->after('alamat_wali');
            $table->string('kartu_keluarga_file')->nullable()->after('akta_kelahiran_file');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'nohp_wali', 'ttl_wali', 'agama_wali', 'pekerjaan_wali',
                'penghasilan_wali', 'alamat_wali', 'akta_kelahiran_file',
                'kartu_keluarga_file',
            ]);
        });
    }
};
