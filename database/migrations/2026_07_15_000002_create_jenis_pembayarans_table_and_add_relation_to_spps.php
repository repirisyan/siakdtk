<?php

use App\Models\JenisPembayaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis')->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreignIdFor(JenisPembayaran::class, 'jenis_pembayaran_id')
                ->nullable()
                ->after('jenis_pembayaran')
                ->constrained('jenis_pembayarans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', fn (Blueprint $table) => $table->dropConstrainedForeignId('jenis_pembayaran_id'));
        Schema::dropIfExists('jenis_pembayarans');
    }
};
