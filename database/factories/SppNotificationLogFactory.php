<?php

namespace Database\Factories;

use App\Models\SppNotificationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SppNotificationLog> */
class SppNotificationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sent_by' => User::factory()->withRole('Staff Administrasi'),
            'batch_id' => (string) Str::uuid(),
            'recipient_count' => fake()->numberBetween(1, 30),
            'source' => 'selected',
            'filters' => ['status' => 'belum_lunas'],
            'sent_at' => now(),
        ];
    }
}
