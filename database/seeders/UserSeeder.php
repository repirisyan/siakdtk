<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminRole = Role::where('role_name', 'Admin')->first();

        if (! $adminRole) {

            return;

        }

        User::firstOrCreate(

            [

                'email' => 'admin@siakdtk.com',
                'email_verified_at' => now(),

            ],

            [

                'role_id' => $adminRole->id,

                'name' => 'Administrator',

                'password' => Hash::make('password'),

                'status' => true,

            ]

        );

    }
}
