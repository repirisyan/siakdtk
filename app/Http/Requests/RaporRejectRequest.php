<?php

namespace App\Http\Requests;

class RaporRejectRequest extends RaporApprovalRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'catatan_validasi' => ['required', 'string', 'max:5000'],
        ];
    }
}
