<?php

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Konten;
use App\Models\KontenGaleri;
use App\Models\MasterKomponenPenilaian;
use App\Models\MidtransTransaction;
use App\Models\Nilai;
use App\Models\NilaiFotoKegiatan;
use App\Models\Rapor;
use App\Models\RaporAkhir;
use App\Models\RaporAkhirDetail;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\Spp;
use App\Models\SppNotificationLog;
use App\Models\SppPembayaran;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;

it('creates valid records from every application factory', function () {
    collect([
        User::class,
        Kelas::class,
        Tema::class,
        SubTema::class,
        Guru::class,
        Siswa::class,
        Jadwal::class,
        Konten::class,
        KontenGaleri::class,
        KomponenPenilaian::class,
        MasterKomponenPenilaian::class,
        Absen::class,
        Nilai::class,
        NilaiFotoKegiatan::class,
        Rapor::class,
        RaporAkhir::class,
        RaporAkhirDetail::class,
        JenisPembayaran::class,
        Spp::class,
        SppPembayaran::class,
        MidtransTransaction::class,
        SppNotificationLog::class,
        SchoolSetting::class,
    ])->each(function (string $model): void {
        $record = $model::factory()->create();

        expect($record->exists)->toBeTrue();
    });
});
