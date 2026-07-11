<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'nik' => ['required', 'string', 'max:20', 'unique:siswas,nik'],
            'nomor_kk' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'tmp_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'jk' => ['required', 'string', 'max:20'],
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'tinggi_bdn' => ['nullable', 'numeric'],
            'berat_bdn' => ['nullable', 'numeric'],
            'anak_ke' => ['nullable', 'string', 'max:2'],
            'jml_sdr' => ['nullable', 'string', 'max:2'],
            'nama_pgl' => ['nullable', 'string', 'max:25'],
            'alamat' => ['required', 'string', 'max:1000'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nohp_ayah' => ['nullable', 'string', 'max:15'],
            'ttl_ayah' => ['nullable', 'date'],
            'agama_ayah' => ['nullable', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'penghasilan' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nohp_ibu' => ['nullable', 'string', 'max:15'],
            'ttl_ibu' => ['nullable', 'date'],
            'agama_ibu' => ['nullable', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $roleOrangtua = Role::firstOrCreate([
                'role_name' => 'Orangtua Siswa',
            ]);

            $user = User::create([
                'role_id' => $roleOrangtua->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'status' => false,
                'email_verified_at' => null,
            ]);

            Siswa::create([
                ...Arr::only($input, [
                    'nik',
                    'nomor_kk',
                    'nama',
                    'tmp_lahir',
                    'tgl_lahir',
                    'jk',
                    'agama',
                    'tinggi_bdn',
                    'berat_bdn',
                    'anak_ke',
                    'jml_sdr',
                    'nama_pgl',
                    'alamat',
                    'nama_ayah',
                    'nohp_ayah',
                    'ttl_ayah',
                    'agama_ayah',
                    'pekerjaan',
                    'penghasilan',
                    'nama_ibu',
                    'nohp_ibu',
                    'ttl_ibu',
                    'agama_ibu',
                    'pekerjaan_ibu',
                    'penghasilan_ibu',
                    'nama_wali',
                ]),
                'user_id' => $user->id,
                'tanggal_registrasi' => today(),
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $user;
        });
    }
}
