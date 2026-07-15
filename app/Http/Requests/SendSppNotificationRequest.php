<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSppNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $user?->loadMissing('role');

        return (bool) ($user?->hasRole('Admin') || $user?->hasRole('Staff Administrasi'));
    }

    public function rules(): array
    {
        return [
            'spp_ids' => ['required', 'array', 'min:1', 'max:1000'],
            'spp_ids.*' => ['integer', 'distinct', 'exists:pembayarans,id'],
        ];
    }
}
