<?php

use App\Models\Kelas;
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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('nik', 20)->unique();
            $table->string('nomor_kk', 20);
            $table->string('nama');
            $table->string('tmp_lahir');
            $table->date('tgl_lahir');
            $table->string('jk', 20);
            $table->string('agama', 20);
            $table->decimal('tinggi_bdn')->nullable();
            $table->decimal('berat_bdn')->nullable();
            $table->char('anak_ke', 2)->nullable();
            $table->char('jml_sdr', 2)->nullable();
            $table->string('nama_pgl', 25)->nullable();
            $table->string('alamat', 1000);
            $table->string('nama_ayah')->nullable();
            $table->string('nohp_ayah', 15)->nullable();
            $table->date('ttl_ayah')->nullable();
            $table->string('agama_ayah', 15)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('penghasilan')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nohp_ibu', 15)->nullable();
            $table->date('ttl_ibu')->nullable();
            $table->string('agama_ibu', 15)->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            $table->foreignIdFor(Kelas::class, 'kelas_id')->nullable();
            $table->foreignIdFor(User::class, 'user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
