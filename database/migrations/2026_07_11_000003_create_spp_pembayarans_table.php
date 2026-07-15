<?php

use App\Models\Spp;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Spp::class, 'pembayaran_id')->constrained('pembayarans')->cascadeOnDelete();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('metode_pembayaran', 50);
            $table->string('bukti_pembayaran')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignIdFor(User::class, 'received_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pembayarans');
    }
};
