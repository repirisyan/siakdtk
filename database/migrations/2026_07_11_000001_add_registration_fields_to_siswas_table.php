<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->date('tanggal_registrasi')->after('user_id');
            $table->enum('status', ['pending', 'aktif', 'ditolak'])
                ->default('pending')
                ->after('tanggal_registrasi');
            $table->foreignIdFor(User::class, 'approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'tanggal_registrasi',
                'status',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
