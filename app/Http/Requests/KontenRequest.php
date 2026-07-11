<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KontenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && ($user->hasRole('Admin') || $user->hasRole('Staff Administrasi'));
    }

    public function rules(): array
    {
        return [
            'jenis_konten' => ['required', Rule::in(['berita', 'event', 'pengumuman', 'galeri'])],
            'judul' => ['required', 'string', 'max:255'],
            'ringkasan' => ['nullable', 'string', 'max:1000'],
            'konten' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tanggal_publish' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'tanggal_event' => ['nullable', 'required_if:jenis_konten,event', 'date'],
            'jam_mulai' => ['nullable', 'required_if:jenis_konten,event', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'required_if:jenis_konten,event', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['nullable', 'required_if:jenis_konten,event', 'string', 'max:255'],
        ];
    }
}
