<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // MySQL uses the existing composite unique index to support the
            // siswa_id foreign key, so create a dedicated index before it is removed.
            $table->index('siswa_id');
            $table->dropUnique('pembayarans_siswa_id_thn_ajaran_unique');
            $table->string('jenis_pembayaran', 100)->default('SPP')->after('thn_ajaran');
            $table->date('tanggal_tagihan')->nullable()->after('jenis_pembayaran');
            $table->date('jatuh_tempo')->nullable()->after('tanggal_tagihan');
            $table->unique(['siswa_id', 'jenis_pembayaran', 'tanggal_tagihan'], 'pembayarans_student_bill_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique('pembayarans_student_bill_unique');
            $table->unique(['siswa_id', 'thn_ajaran']);
            $table->dropIndex('pembayarans_siswa_id_index');
            $table->dropColumn(['jenis_pembayaran', 'tanggal_tagihan', 'jatuh_tempo']);
        });
    }
};
