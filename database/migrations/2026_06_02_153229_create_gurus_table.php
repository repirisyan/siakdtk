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
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id');
            $table->string('nama');
            $table->string('nip', 20)->unique();
            $table->string('tmp_lhr');
            $table->date('tgl_lahir');
            $table->string('alamat', 1000);
            $table->string('agama')->nullable();
            $table->string('nohp_guru')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('jk', 20)->nullable();
            $table->string('jenis_ptk')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('stts_kepegawaian')->nullable();
            $table->string('sk_cpns')->nullable();
            $table->string('tgl_cpns')->nullable();
            $table->string('sk_pengangkatan')->nullable();
            $table->string('tmt_pengangkatan')->nullable();
            $table->string('pangkat_golongan')->nullable();
            $table->string('tmt_pns')->nullable();
            $table->string('npwp')->unique()->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
