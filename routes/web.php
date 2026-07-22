<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KomponenPenilaianController;
use App\Http\Controllers\KontenController;
use App\Http\Controllers\KontenGaleriController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MasterKomponenPenilaianController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\RaporAkhirController;
use App\Http\Controllers\RaporAnakController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SppController;
use App\Http\Controllers\SppPembayaranController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\SubTemaController;
use App\Http\Controllers\TagihanSayaController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('berita/{konten:slug}', [LandingPageController::class, 'showNews'])->name('berita.show');
Route::post('midtrans/callback', MidtransCallbackController::class)->name('midtrans.callback');
Route::get('email/verify-public/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.public-verify');
Route::post('email/verification-notification-public', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:6,1')
    ->name('verification.public-resend');
Route::middleware('guest')->group(function () {
    Route::get('register', [StudentRegistrationController::class, 'create'])->name('register');
    Route::post('register', [StudentRegistrationController::class, 'store'])->name('register.store');
    Route::get('register/success', [StudentRegistrationController::class, 'success'])->name('register.success');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('pengaturan-sekolah', [SchoolSettingController::class, 'edit'])->name('school-settings.edit');
    Route::put('pengaturan-sekolah', [SchoolSettingController::class, 'update'])->name('school-settings.update');
    Route::get('tambah-anak', [StudentRegistrationController::class, 'createAdditional'])->name('anak.create');
    Route::post('tambah-anak', [StudentRegistrationController::class, 'storeAdditional'])->name('anak.store');
    Route::resource('tema', TemaController::class);
    Route::resource('sub-tema', SubTemaController::class);
    Route::resource('komponen-penilaian', KomponenPenilaianController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->parameters(['komponen-penilaian' => 'komponenPenilaian']);
    Route::resource('master-komponen-penilaian', MasterKomponenPenilaianController::class)
        ->parameters(['master-komponen-penilaian' => 'masterKomponenPenilaian']);
    Route::patch('tema/{tema}/toggle-status', [TemaController::class, 'toggleStatus'])->name('tema.toggle-status');
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::delete('users/{user}/photo', [UserController::class, 'destroyPhoto'])
        ->name('users.photo.destroy');
    Route::post('users/{user}/verify-email', [UserController::class, 'verifyEmail'])
        ->name('users.verify-email');
    Route::post('users/{user}/resend-verification-email', [UserController::class, 'resendVerificationEmail'])
        ->name('users.resend-verification-email');
    Route::get('siswa/export', [SiswaController::class, 'exportData'])->name('siswa.export');
    Route::get('siswa/import-template', [SiswaController::class, 'downloadImportTemplate'])->name('siswa.import-template');
    Route::post('siswa/import', [SiswaController::class, 'importData'])->name('siswa.import');
    Route::resource('siswa', SiswaController::class);
    Route::resource('spp', SppController::class);
    Route::resource('jenis-pembayaran', JenisPembayaranController::class)
        ->except(['show']);
    Route::resource('konten', KontenController::class);
    Route::post('konten/{konten}/galeri', [KontenGaleriController::class, 'store'])
        ->name('konten.galeri.store');
    Route::delete('konten/{konten}/galeri/{galeri}', [KontenGaleriController::class, 'destroy'])
        ->name('konten.galeri.destroy');
    Route::post('spp/generate', [SppController::class, 'generate'])->name('spp.generate');
    Route::post('spp/{spp}/send-notification', [SppController::class, 'sendNotification'])->name('spp.send-notification');
    Route::post('spp/send-notifications', [SppController::class, 'sendNotifications'])->name('spp.send-notifications');
    Route::post('spp/send-notifications-by-filter', [SppController::class, 'sendNotificationsByFilter'])->name('spp.send-notifications-by-filter');
    Route::post('spp/{spp}/payments', [SppPembayaranController::class, 'store'])
        ->name('spp.payments.store');
    Route::put('spp/{spp}/payments/{payment}', [SppPembayaranController::class, 'update'])
        ->name('spp.payments.update');
    Route::delete('spp/{spp}/payments/{payment}', [SppPembayaranController::class, 'destroy'])
        ->name('spp.payments.destroy');
    Route::post('spp/{spp}/payments/{payment}/approve', [SppPembayaranController::class, 'approve'])->name('spp.payments.approve');
    Route::post('spp/{spp}/payments/{payment}/reject', [SppPembayaranController::class, 'reject'])->name('spp.payments.reject');
    Route::get('tagihan-saya', [TagihanSayaController::class, 'index'])->name('tagihan-saya.index');
    Route::get('tagihan-saya/{spp}', [TagihanSayaController::class, 'show'])->name('tagihan-saya.show');
    Route::post('tagihan-saya/{spp}/manual-payment', [TagihanSayaController::class, 'storeManualPayment'])->name('tagihan-saya.manual-payment');
    Route::post('tagihan-saya/{spp}/midtrans', [TagihanSayaController::class, 'createMidtransTransaction'])->name('tagihan-saya.midtrans');
    Route::post('siswa/{siswa}/move-class', [SiswaController::class, 'moveClass'])
        ->name('siswa.move-class');
    Route::post('siswa/{siswa}/approve', [SiswaController::class, 'approve'])
        ->name('siswa.approve');
    Route::post('siswa/{siswa}/reject', [SiswaController::class, 'reject'])
        ->name('siswa.reject');
    Route::resource('kelas', KelasController::class)->parameters([
        'kelas' => 'kelas',
    ]);
    Route::patch('kelas/{kelas}/toggle-status', [KelasController::class, 'toggleStatus'])->name('kelas.toggle-status');
    Route::post('kelas/{kelas}/move-students', [KelasController::class, 'moveStudents'])
        ->name('kelas.move-students');
    Route::resource('guru', GuruController::class)->parameters([
        'guru' => 'guru',
    ]);
    Route::put('guru/{guru}/toggle-status', [GuruController::class, 'toggleStatus'])
        ->name('guru.toggle-status');
    Route::resource('jadwal', JadwalController::class)->parameters([
        'jadwal' => 'jadwal',
    ]);
    Route::resource('absensi', AbsenController::class)->parameters([
        'absensi' => 'absen',
    ])->only(['index', 'store', 'destroy']);
    Route::get('penilaian/summary/{siswa}', [PenilaianController::class, 'summary'])
        ->name('penilaian.summary');
    Route::resource('penilaian', PenilaianController::class)->only(['index', 'store']);
    Route::resource('rapor', RaporController::class)->only(['index', 'store', 'show', 'update']);
    Route::get('hasil-akhir-rapor', [RaporAkhirController::class, 'index'])->name('rapor-akhir.index');
    Route::post('hasil-akhir-rapor', [RaporAkhirController::class, 'store'])->name('rapor-akhir.store');
    Route::post('hasil-akhir-rapor/{raporAkhir}/submit', [RaporAkhirController::class, 'submit'])->name('rapor-akhir.submit');
    Route::post('hasil-akhir-rapor/{raporAkhir}/approve', [RaporAkhirController::class, 'approve'])->name('rapor-akhir.approve');
    Route::post('hasil-akhir-rapor/{raporAkhir}/reject', [RaporAkhirController::class, 'reject'])->name('rapor-akhir.reject');
    Route::post('hasil-akhir-rapor/approve-all', [RaporAkhirController::class, 'approveAll'])->name('rapor-akhir.approve-all');
    Route::get('hasil-akhir-rapor/kelas/{kelas}/cetak', [RaporAkhirController::class, 'printClass'])->name('rapor-akhir.print-class');
    Route::get('hasil-akhir-rapor/{raporAkhir}/cetak', [RaporAkhirController::class, 'printStudent'])->name('rapor-akhir.print-student');
    Route::resource('rapor-anak', RaporAnakController::class)
        ->parameters(['rapor-anak' => 'rapor'])
        ->only(['index', 'show']);
    Route::post('rapor/submit', [RaporController::class, 'submit'])->name('rapor.submit');
    Route::get('validasi-rapor', [RaporController::class, 'validationIndex'])
        ->name('rapor.validation.index');
    Route::post('validasi-rapor/siswa/{siswa}/approve', [RaporController::class, 'approve'])
        ->name('rapor.approve');
    Route::post('validasi-rapor/siswa/{siswa}/reject', [RaporController::class, 'reject'])
        ->name('rapor.reject');
    Route::post('validasi-rapor/approve-all', [RaporController::class, 'approveAll'])
        ->name('rapor.approve-all');
});

require __DIR__.'/settings.php';
