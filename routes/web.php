<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PeringatanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\UploadController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// Hanya untuk tamu (belum login)
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/peringatan', [PeringatanController::class, 'index'])
    ->name('peringatan');
// Halaman publik (tanpa login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.proses');
});

// Hanya untuk yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---- Tahap B ----
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data-satker', [SatkerController::class, 'index'])->name('data-satker');

    // ---- Tahap C ----
    Route::get('/upload', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
    Route::delete('/upload/{periode}', [UploadController::class, 'destroy'])
        ->where('periode', '[0-9A-Za-z\-]+')
        ->name('upload.destroy');

    // ---- Tahap D ----
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

    // ---- Tahap E: profil & pengaturan akun (semua user yang login) ----
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::put('/pengaturan/username', [PengaturanController::class, 'updateUsername'])->name('pengaturan.username');
    Route::put('/pengaturan/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.password');

    // ---- Tahap E: khusus ADMIN ----
    Route::middleware(AdminOnly::class)->group(function () {
        // Backup & restore database
        Route::post('/pengaturan/backup', [BackupController::class, 'buat'])->name('backup.buat');
        Route::post('/pengaturan/restore', [BackupController::class, 'pulihkan'])->name('backup.pulihkan');
        Route::get('/pengaturan/backup/{nama}', [BackupController::class, 'unduh'])
            ->where('nama', '[A-Za-z0-9_\-\.]+\.sql')->name('backup.unduh');
        Route::delete('/pengaturan/backup/{nama}', [BackupController::class, 'hapus'])
            ->where('nama', '[A-Za-z0-9_\-\.]+\.sql')->name('backup.hapus');

        // Approval user
        Route::get('/admin/approval', [ApprovalController::class, 'index'])->name('admin.approval');
        Route::post('/admin/approval/{pengguna}/status', [ApprovalController::class, 'ubahStatus'])->name('admin.approval.status');
        Route::delete('/admin/approval/{pengguna}', [ApprovalController::class, 'destroy'])->name('admin.approval.hapus');
    });
});
