<?php

namespace Database\Factories;

use App\Models\MidtransTransaction;
use App\Models\Spp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MidtransTransaction> */
class MidtransTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'spp_id' => Spp::factory(),
            'user_id' => User::factory()->orangTua(),
            'order_id' => 'SPP-'.fake()->unique()->uuid(),
            'snap_token' => fake()->sha1(),
            'gross_amount' => 300000,
            'payment_type' => 'bank_transfer',
            'transaction_status' => 'pending',
            'response_payload' => ['status_message' => 'Data transaksi dummy.'],
        ];
    }
}
