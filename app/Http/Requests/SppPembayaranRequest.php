<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SppPembayaranRequest extends FormRequest
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
            'tanggal_bayar' => ['required', 'date'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0.01'],
            'metode_pembayaran' => ['required', 'string', 'max:50'],
            'bukti_pembayaran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
