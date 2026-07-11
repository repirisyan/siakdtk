<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $user->loadMissing('role');

        return $user->hasRole('Admin');
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => $user
                ? ['nullable', 'string', 'confirmed', 'min:8']
                : ['required', 'string', 'confirmed', 'min:8'],
            'status' => ['required', 'boolean'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
