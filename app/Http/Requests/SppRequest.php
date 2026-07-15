<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SppRequest extends FormRequest
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
        $spp = $this->route('spp');

        return [
            'siswa_id' => ['required', 'integer', Rule::exists('siswas', 'id')->where(fn ($query) => $query->where('status', 'aktif')->whereExists(function ($subQuery) {
                $subQuery->selectRaw('1')->from('kelas')->whereColumn('kelas.id', 'siswas.kelas_id')->where('status', true);
            }))],
            'thn_ajaran' => [
                'required',
                'digits:4',
            ],
            'jenis_pembayaran_id' => ['required', 'integer', Rule::exists('jenis_pembayarans', 'id')->where('status', true)],
            'tanggal_tagihan' => [
                'required',
                'date',
                Rule::unique('pembayarans', 'tanggal_tagihan')
                    ->where(fn ($query) => $query
                        ->where('siswa_id', $this->input('siswa_id'))
                        ->where('jenis_pembayaran', function ($query) {
                            $query->select('nama_jenis')->from('jenis_pembayarans')->where('id', $this->input('jenis_pembayaran_id'));
                        }))
                    ->ignore($spp),
            ],
            'jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_tagihan'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
