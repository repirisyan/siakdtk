<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentSppPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $user?->loadMissing(['role', 'siswas']);

        return (bool) ($user?->hasRole('Orangtua Siswa') && $user->siswas->isNotEmpty());
    }

    public function rules(): array
    {
        return [
            'tanggal_bayar' => ['required', 'date'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0.01'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
