<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $user?->loadMissing('role');

        return (bool) ($user?->hasRole('Admin') || $user?->hasRole('Staff Akademik'));
    }

    public function rules(): array
    {
        return [
            'thn_ajaran' => ['required', 'digits:4', Rule::exists('kelas', 'thn_ajaran')->where('status', true)],
            'kelas_id' => [
                'required',
                'integer',
                Rule::exists('kelas', 'id')->where(
                    fn ($query) => $query->where('thn_ajaran', $this->input('thn_ajaran'))->where('status', true),
                ),
            ],
        ];
    }
}
