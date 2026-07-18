<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $agama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $isExistingParent = $this->user()?->hasRole('Orangtua Siswa') ?? false;

        return [
            'name' => [$isExistingParent ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => [$isExistingParent ? 'nullable' : 'required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [$isExistingParent ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'akta_kelahiran_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'kartu_keluarga_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'nik' => ['required', 'string', 'max:20', 'unique:siswas,nik'],
            'nomor_kk' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'tmp_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'jk' => ['required', 'string', 'max:20'],
            'agama' => ['required', 'string', Rule::in($agama)],
            'tinggi_bdn' => ['nullable', 'numeric'],
            'berat_bdn' => ['nullable', 'numeric'],
            'anak_ke' => ['nullable', 'string', 'max:2'],
            'jml_sdr' => ['nullable', 'string', 'max:2'],
            'nama_pgl' => ['nullable', 'string', 'max:25'],
            'alamat' => ['required', 'string', 'max:1000'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nohp_ayah' => ['nullable', 'string', 'max:15'],
            'ttl_ayah' => ['nullable', 'date'],
            'agama_ayah' => ['nullable', 'string', Rule::in($agama)],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'penghasilan' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nohp_ibu' => ['nullable', 'string', 'max:15'],
            'ttl_ibu' => ['nullable', 'date'],
            'agama_ibu' => ['nullable', 'string', Rule::in($agama)],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'nohp_wali' => ['nullable', 'string', 'max:15'],
            'ttl_wali' => ['nullable', 'date'],
            'agama_wali' => ['nullable', 'string', Rule::in($agama)],
            'pekerjaan_wali' => ['nullable', 'string', 'max:255'],
            'penghasilan_wali' => ['nullable', 'string', 'max:255'],
            'alamat_wali' => ['nullable', 'string', 'max:1000'],
            'desa_wali' => ['nullable', 'string', 'max:255'],
            'kecamatan_wali' => ['nullable', 'string', 'max:255'],
            'kabupaten_wali' => ['nullable', 'string', 'max:255'],
            'provinsi_wali' => ['nullable', 'string', 'max:255'],
        ];
    }
}
