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

        collect([
            ['name' => 'Administrator', 'email' => 'admin@siakdtk.test', 'role' => 'Admin'],
            ['name' => 'Staff Akademik', 'email' => 'akademik@siakdtk.test', 'role' => 'Staff Akademik'],
            ['name' => 'Staff Administrasi', 'email' => 'administrasi@siakdtk.test', 'role' => 'Staff Administrasi'],
            ['name' => 'Kepala Sekolah', 'email' => 'kepsek@siakdtk.test', 'role' => 'Kepsek'],
        ])->each(function (array $account): void {
            $role = Role::where('role_name', $account['role'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role_id' => $role->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => true,
                ],
            );
        });

    }
}
