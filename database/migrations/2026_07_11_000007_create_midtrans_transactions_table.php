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
        Schema::create('midtrans_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Spp::class, 'spp_id')->constrained('spps')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('payment_type')->nullable();
            $table->string('transaction_status')->default('pending');
            $table->json('response_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_transactions');
    }
};
