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
        Schema::table('rapors', function (Blueprint $table) {
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])
                ->default('draft')
                ->after('keterangan');
            $table->foreignIdFor(User::class, 'validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('status');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            $table->text('catatan_validasi')->nullable()->after('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'status',
                'validated_by',
                'validated_at',
                'catatan_validasi',
            ]);
        });
    }
};
