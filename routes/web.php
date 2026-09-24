<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DispensasiController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\VerifikasiSuratController;
use App\Http\Controllers\Sdm\AlurApprovalController;
use App\Http\Controllers\Sdm\DashboardController;
use App\Http\Controllers\Sdm\MonitoringController;
use App\Http\Controllers\Sdm\PegawaiController;
use App\Http\Controllers\Sdm\PegawaiImportController;
use App\Http\Controllers\Sdm\UserController;
use App\Http\Controllers\Sdm\ArsipEDispensasiController;
use App\Http\Controllers\Sdm\UnitOrganisasiController;
use App\Http\Controllers\Sdm\JabatanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? auth()->user()->dashboardRoute() : route('login'));
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/verifikasi/{token}', [VerifikasiSuratController::class, 'show'])->name('verifikasi.surat');

Route::middleware('auth')->group(function () {
    Route::get('/ganti-password', [PasswordChangeController::class, 'showForm'])->name('password.change.form');
    Route::post('/ganti-password', [PasswordChangeController::class, 'update'])->name('password.change.update');

    Route::post('/notifikasi/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifikasi.markAllRead');

    Route::get('/notifikasi/{notifikasi}/buka', function (\Illuminate\Notifications\DatabaseNotification $notifikasi) {
        abort_unless($notifikasi->notifiable_id === auth()->id(), 403);

        $notifikasi->markAsRead();

        return redirect($notifikasi->data['url'] ?? route('login'));
    })->name('notifikasi.buka');
});

Route::middleware(['auth', 'role:admin_departemen'])->group(function () {
    Route::get('/dispensasi/create', [DispensasiController::class, 'create'])->name('dispensasi.create');
    Route::post('/dispensasi', [DispensasiController::class, 'store'])->name('dispensasi.store');
    Route::get('/dispensasi', [DispensasiController::class, 'index'])->name('dispensasi.index');
    Route::get('/dispensasi/export-pdf', [DispensasiController::class, 'exportPdf'])->name('dispensasi.export.pdf');
    Route::get('/dispensasi/{dispensasi}', [DispensasiController::class, 'show'])->name('dispensasi.show');
    Route::get('/dispensasi/{dispensasi}/cetak', [DispensasiController::class, 'cetakForm'])->name('dispensasi.cetak.form');
    Route::post('/dispensasi/{dispensasi}/cetak', [DispensasiController::class, 'cetakStore'])->name('dispensasi.cetak.store');
});

Route::middleware(['auth', 'role:approver'])->group(function () {
    Route::get('/persetujuan', [ApprovalController::class, 'index'])->name('approval.index');
    Route::get('/persetujuan/{dispensasi}', [ApprovalController::class, 'show'])->name('approval.show');
    Route::post('/dispensasi/{dispensasi}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/dispensasi/{dispensasi}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');

    Route::get('/approval/dashboard', [ApprovalController::class, 'index'])->name('dashboard.approver');
});

Route::middleware(['auth', 'role:admin_sdm'])->prefix('sdm')->name('sdm.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('pegawai', PegawaiController::class)->except(['show']);

    Route::resource('jabatan', JabatanController::class)->except(['show']);

    Route::get('/pegawai-import', [PegawaiImportController::class, 'form'])->name('pegawai.import.form');
    Route::post('/pegawai-import/preview', [PegawaiImportController::class, 'preview'])->name('pegawai.import.preview');
    Route::post('/pegawai-import/confirm', [PegawaiImportController::class, 'confirm'])->name('pegawai.import.confirm');

    Route::get('/unit-organisasi/list', [UnitOrganisasiController::class, 'list'])->name('unit-organisasi.list');
    Route::get('/unit-organisasi/tree', [UnitOrganisasiController::class, 'tree'])->name('unit-organisasi.tree');
    Route::get('/unit-organisasi/create', [UnitOrganisasiController::class, 'create'])->name('unit-organisasi.create');
    Route::post('/unit-organisasi', [UnitOrganisasiController::class, 'store'])->name('unit-organisasi.store');
    Route::get('/unit-organisasi', [UnitOrganisasiController::class, 'index'])->name('unit-organisasi.index');
    Route::get('/unit-organisasi/{unitOrganisasi}/edit', [UnitOrganisasiController::class, 'edit'])->name('unit-organisasi.edit');
    Route::get('/unit-organisasi/{id}', [UnitOrganisasiController::class, 'show'])->name('unit-organisasi.show');
    Route::put('/unit-organisasi/{unitOrganisasi}', [UnitOrganisasiController::class, 'update'])->name('unit-organisasi.update');
    Route::delete('/unit-organisasi/{unitOrganisasi}', [UnitOrganisasiController::class, 'destroy'])->name('unit-organisasi.destroy');
    Route::patch('/unit-organisasi/{unitOrganisasi}/aktifkan', [UnitOrganisasiController::class, 'aktifkan'])->name('unit-organisasi.aktifkan');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/export-excel', [MonitoringController::class, 'exportExcel'])->name('monitoring.export.excel');
    Route::get('/monitoring/{dispensasi}', [MonitoringController::class, 'show'])->name('monitoring.show');

    Route::resource('pengguna', UserController::class)->except(['show']);

    Route::get('/arsip-e-dispensasi', [ArsipEDispensasiController::class, 'index'])->name('arsip-e-dispensasi.index');
    Route::get('/arsip-e-dispensasi/{dispensasi}', [ArsipEDispensasiController::class, 'show'])->name('arsip-e-dispensasi.show');

    Route::get('/alur-approval', [AlurApprovalController::class, 'index'])->name('alur-approval.index');
    Route::get('/alur-approval/{unitOrganisasi}', [AlurApprovalController::class, 'edit'])->name('alur-approval.edit');
    Route::put('/alur-approval/{unitOrganisasi}', [AlurApprovalController::class, 'update'])->name('alur-approval.update');
});

Route::middleware(['auth', 'role:admin_departemen,admin_sdm'])->group(function () {
    Route::get('/dispensasi/{dispensasi}/surat', [DispensasiController::class, 'unduhSurat'])->name('dispensasi.surat.unduh');
});