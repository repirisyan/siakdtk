<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSppRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('role');

        return $user->hasRole('Admin') || $user->hasRole('Staff Administrasi');
    }

    public function rules(): array
    {
        return [
            'target' => ['required', 'in:kelas,tahun_ajaran'],
            'kelas_id' => ['nullable', 'required_if:target,kelas', 'integer', 'exists:kelas,id'],
            'thn_ajaran' => ['nullable', 'required_if:target,tahun_ajaran', 'digits:4', 'exists:kelas,thn_ajaran'],
            'jenis_pembayaran_id' => ['required', 'integer', 'exists:jenis_pembayarans,id,status,1'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'tanggal_tagihan' => ['required', 'date'],
            'jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_tagihan'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
