<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_pembayarans', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['manual', 'midtrans'])->default('manual')->change();
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending')->after('metode_pembayaran');
            $table->foreignIdFor(User::class, 'verified_by')->nullable()->constrained('users')->nullOnDelete()->after('status_verifikasi');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('spp_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status_verifikasi', 'verified_by', 'verified_at']);
            $table->string('metode_pembayaran', 50)->change();
        });
    }
};
