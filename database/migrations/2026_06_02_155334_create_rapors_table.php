<?php

use App\Models\Guru;
use App\Models\Tema;
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
        Schema::create('rapors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(Guru::class, 'guru_id');
            $table->foreignIdFor(Tema::class, 'tema_id');
            $table->text('keterangan')->nullable();
            $table->year('thn_ajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapors');
    }
};
