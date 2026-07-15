<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('role_name', 'Guru')->firstOrFail();

        collect([
            ['nama' => 'Ibu Sari Wulandari', 'email' => 'guru.sari@siakdtk.test', 'nip' => '198701012010012001'],
            ['nama' => 'Bapak Andi Pratama', 'email' => 'guru.andi@siakdtk.test', 'nip' => '198802022011012002'],
            ['nama' => 'Ibu Rina Lestari', 'email' => 'guru.rina@siakdtk.test', 'nip' => '198903032012012003'],
        ])->each(function (array $data) use ($role): void {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nama'],
                    'role_id' => $role->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => true,
                ],
            );

            Guru::updateOrCreate(
                ['nip' => $data['nip']],
                [
                    'user_id' => $user->id,
                    'nama' => $data['nama'],
                    'tmp_lhr' => 'Jakarta',
                    'tgl_lahir' => '1987-01-01',
                    'alamat' => 'Jl. Pendidikan No. 1',
                    'agama' => 'Islam',
                    'nohp_guru' => '0812'.substr($data['nip'], -8),
                    'email' => $data['email'],
                    'jk' => str_contains($data['nama'], 'Ibu') ? 'P' : 'L',
                    'jenis_ptk' => 'Guru Kelas',
                    'pendidikan' => 'S1',
                    'stts_kepegawaian' => 'Honorer',
                ],
            );
        });
    }
}
