<?php

namespace Database\Factories;

use App\Models\Spp;
use App\Models\SppPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SppPembayaran> */
class SppPembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pembayaran_id' => Spp::factory(),
            'tanggal_bayar' => today(),
            'jumlah_bayar' => 150000,
            'metode_pembayaran' => 'manual',
            'status_verifikasi' => 'approved',
            'received_by' => User::factory()->withRole('Staff Administrasi'),
            'verified_by' => User::factory()->withRole('Staff Administrasi'),
            'verified_at' => now(),
            'keterangan' => 'Pembayaran dummy.',
        ];
    }
}
