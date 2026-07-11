<?php

namespace App\Http\Requests;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MoveSiswaClassRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $siswa = $this->route('siswa');

        $this->merge([
            'kelas_lama_id' => $siswa?->kelas_id,
        ]);
    }

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
        return [
            // Nullable: siswa yang baru daftar belum punya kelas sama sekali.
            'kelas_lama_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'kelas_id' => ['required', 'integer', Rule::exists('kelas', 'id')->where('status', true), 'different:kelas_lama_id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $siswa = $this->route('siswa');
            $kelasTujuan = Kelas::find($this->input('kelas_id'));

            // Kalau siswa belum punya kelas sama sekali, tidak ada tahun ajaran
            // untuk dicocokkan — kelas tujuan apa pun boleh dipilih (penetapan pertama).
            if (
                $siswa &&
                $siswa->kelas &&
                $kelasTujuan &&
                $siswa->kelas->thn_ajaran !== $kelasTujuan->thn_ajaran
            ) {
                $validator->errors()->add('kelas_id', 'Kelas tujuan harus berada pada tahun ajaran yang sama.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'kelas_id.different' => 'Kelas tujuan harus berbeda dengan kelas saat ini.',
        ];
    }
}
