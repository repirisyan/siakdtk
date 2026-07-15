<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->timestamp('last_notification_at')->nullable()->after('keterangan');
            $table->foreignIdFor(User::class, 'last_notified_by')->nullable()->after('last_notification_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['last_notified_by']);
            $table->dropColumn(['last_notification_at', 'last_notified_by']);
        });
    }
};
