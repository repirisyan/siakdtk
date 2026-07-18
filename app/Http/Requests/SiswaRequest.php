<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    private const AGAMA_OPTIONS = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
    ];

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('role');

        return $user->hasRole('Admin') || $user->hasRole('Staff Akademik');
    }

    public function rules(): array
    {
        $siswa = $this->route('siswa');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($siswa?->user_id)],
            'password' => [$siswa ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'akta_kelahiran_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'kartu_keluarga_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'nis' => ['nullable', 'string', 'max:20'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['required', 'string', 'max:20', Rule::unique('siswas', 'nik')->ignore($this->route('siswa'))],
            'nomor_kk' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'tmp_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'jk' => ['required', 'string', 'max:20'],
            'agama' => ['required', 'string', Rule::in(self::AGAMA_OPTIONS)],
            'tinggi_bdn' => ['nullable', 'numeric'],
            'berat_bdn' => ['nullable', 'numeric'],
            'anak_ke' => ['nullable', 'string', 'max:2'],
            'jml_sdr' => ['nullable', 'string', 'max:2'],
            'nama_pgl' => ['nullable', 'string', 'max:25'],
            'alamat' => ['required', 'string', 'max:1000'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nohp_ayah' => ['nullable', 'string', 'max:15'],
            'ttl_ayah' => ['nullable', 'date'],
            'agama_ayah' => ['nullable', 'string', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'penghasilan' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nohp_ibu' => ['nullable', 'string', 'max:15'],
            'ttl_ibu' => ['nullable', 'date'],
            'agama_ibu' => ['nullable', 'string', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'nohp_wali' => ['nullable', 'string', 'max:15'],
            'ttl_wali' => ['nullable', 'date'],
            'agama_wali' => ['nullable', 'string', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan_wali' => ['nullable', 'string', 'max:255'],
            'penghasilan_wali' => ['nullable', 'string', 'max:255'],
            'alamat_wali' => ['nullable', 'string', 'max:1000'],
            'desa_wali' => ['nullable', 'string', 'max:255'],
            'kecamatan_wali' => ['nullable', 'string', 'max:255'],
            'kabupaten_wali' => ['nullable', 'string', 'max:255'],
            'provinsi_wali' => ['nullable', 'string', 'max:255'],
            'thn_ajaran' => ['required', 'digits:4', 'exists:kelas,thn_ajaran'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'kelas_id' => [
                'required',
                'integer',
                Rule::exists('kelas', 'id')->where(
                    fn ($query) => $query
                        ->where('thn_ajaran', $this->input('thn_ajaran'))
                        ->where('semester', $this->integer('semester')),
                ),
            ],
        ];
    }
}
