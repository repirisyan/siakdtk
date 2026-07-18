<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'nik',
        'nomor_kk',
        'nama',
        'tmp_lahir',
        'tgl_lahir',
        'jk',
        'agama',
        'tinggi_bdn',
        'berat_bdn',
        'anak_ke',
        'jml_sdr',
        'nama_pgl',
        'alamat',
        'nama_ayah',
        'nohp_ayah',
        'ttl_ayah',
        'agama_ayah',
        'pekerjaan',
        'penghasilan',
        'nama_ibu',
        'nohp_ibu',
        'ttl_ibu',
        'agama_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'nama_wali',
        'nohp_wali',
        'ttl_wali',
        'agama_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
        'alamat_wali',
        'desa_wali',
        'kecamatan_wali',
        'kabupaten_wali',
        'provinsi_wali',
        'akta_kelahiran_file',
        'kartu_keluarga_file',
        'kelas_id',
        'user_id',
        'tanggal_registrasi',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_registrasi' => 'date',
            'approved_at' => 'datetime',
            'ttl_wali' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Siswa $siswa) {
            $siswa->tanggal_registrasi ??= today();
            $siswa->status ??= 'pending';
        });
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function spps(): HasMany
    {
        return $this->hasMany(Spp::class);
    }

    public function absen(): HasMany
    {
        return $this->hasMany(Absen::class);
    }

    public function absens(): HasMany
    {
        return $this->hasMany(Absen::class);
    }

    public function rapor(): HasMany
    {
        return $this->hasMany(Rapor::class, 'siswa_id');
    }
}
