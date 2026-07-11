<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'sent_by')->constrained('users')->cascadeOnDelete();
            $table->uuid('batch_id')->nullable()->index();
            $table->unsignedInteger('recipient_count');
            $table->string('source', 20);
            $table->json('filters')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp_notification_logs');
    }
};
