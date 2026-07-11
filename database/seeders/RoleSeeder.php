<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [

            'Admin',

            'Staff Akademik',

            'Staff Administrasi',

            'Guru',

            'Kepsek',

            'Orangtua Siswa',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([

                'role_name' => $role,

            ]);

        }
    }
}
