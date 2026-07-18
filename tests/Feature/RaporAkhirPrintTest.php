<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RaporAkhir;
use App\Models\RaporAkhirDetail;
use App\Models\Siswa;
use App\Models\Tema;
use App\Models\User;

it('renders approved final reports in the print view', function () {
    $admin = User::factory()->withRole('Admin')->create();
    $kelas = Kelas::factory()->create([
        'thn_ajaran' => now()->year,
        'semester' => 2,
    ]);
    $siswa = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'desa_wali' => 'Sukamaju',
        'kecamatan_wali' => 'Cianjur',
        'kabupaten_wali' => 'Cianjur',
        'provinsi_wali' => 'Jawa Barat',
    ]);
    $tema = Tema::factory()->create(['thn_ajaran' => $kelas->thn_ajaran]);
    $guru = Guru::factory()->create();
    $rapor = RaporAkhir::create([
        'siswa_id' => $siswa->id,
        'kelas_id' => $kelas->id,
        'thn_ajaran' => $kelas->thn_ajaran,
        'status' => 'disetujui',
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);
    RaporAkhirDetail::create([
        'rapor_akhir_id' => $rapor->id,
        'tema_id' => $tema->id,
        'guru_id' => $guru->id,
        'keterangan' => 'Perkembangan siswa terlihat baik.',
    ]);

    $this->actingAs($admin)
        ->get(route('rapor-akhir.print-student', $rapor))
        ->assertOk()
        ->assertSee('Laporan Capaian Pembelajaran Anak')
        ->assertSee('Tahun Ajaran '.$kelas->thn_ajaran)
        ->assertSee('Semester</span>: Dua', false)
        ->assertSee($siswa->nama)
        ->assertSee('Desa / Kelurahan Orang Tua / Wali')
        ->assertSee('Sukamaju')
        ->assertSee('Jawa Barat')
        ->assertSee('Pas Foto')
        ->assertSee('Kepala Taman Kanak-Kanak')
        ->assertSee('NIP')
        ->assertSee('Pertumbuhan, Kesehatan, dan Kehadiran');
});
