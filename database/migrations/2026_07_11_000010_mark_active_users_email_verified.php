<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('status', true)
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Existing verified timestamps must not be removed on rollback.
    }
};
