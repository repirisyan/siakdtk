<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelas = Kelas::active()->orderBy('id')->get();
        $role = Role::where('role_name', 'Orangtua Siswa')->firstOrFail();
        $adminId = User::whereHas('role', fn ($query) => $query->where('role_name', 'Admin'))->value('id');

        foreach (range(1, 24) as $nomor) {
            $nama = "Siswa Dummy {$nomor}";
            $user = User::updateOrCreate(
                ['email' => "orangtua{$nomor}@siakdtk.test"],
                [
                    'name' => "Orangtua {$nama}",
                    'role_id' => $role->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => true,
                ],
            );

            Siswa::updateOrCreate(
                ['nik' => str_pad((string) $nomor, 16, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $user->id,
                    'kelas_id' => $kelas[($nomor - 1) % $kelas->count()]->id,
                    'tanggal_registrasi' => now()->subDays($nomor),
                    'status' => 'aktif',
                    'approved_by' => $adminId,
                    'approved_at' => now(),
                    'nis' => 'TK'.str_pad((string) $nomor, 4, '0', STR_PAD_LEFT),
                    'nisn' => '20'.str_pad((string) $nomor, 8, '0', STR_PAD_LEFT),
                    'nomor_kk' => '32'.str_pad((string) $nomor, 14, '0', STR_PAD_LEFT),
                    'nama' => $nama,
                    'tmp_lahir' => 'Bandung',
                    'tgl_lahir' => now()->subYears(5)->subDays($nomor)->toDateString(),
                    'jk' => $nomor % 2 ? 'L' : 'P',
                    'agama' => 'Islam',
                    'tinggi_bdn' => 105,
                    'berat_bdn' => 18,
                    'anak_ke' => '1',
                    'jml_sdr' => '1',
                    'nama_pgl' => "Anak {$nomor}",
                    'alamat' => 'Jl. Melati No. '.$nomor,
                    'nama_ayah' => "Ayah {$nama}",
                    'nohp_ayah' => '0813'.str_pad((string) $nomor, 8, '0', STR_PAD_LEFT),
                    'agama_ayah' => 'Islam',
                    'pekerjaan' => 'Wiraswasta',
                    'penghasilan' => '3 - 5 juta',
                    'nama_ibu' => "Ibu {$nama}",
                    'nohp_ibu' => '0814'.str_pad((string) $nomor, 8, '0', STR_PAD_LEFT),
                    'agama_ibu' => 'Islam',
                    'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                    'penghasilan_ibu' => '1 - 3 juta',
                ],
            );
        }
    }
}
