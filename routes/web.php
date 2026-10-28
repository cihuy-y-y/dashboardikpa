<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// Hanya untuk tamu (belum login)
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
    Route::get('/', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data-satker', [SatkerController::class, 'index'])->name('data-satker');

    // ---- Tahap C ----
    Route::get('/upload', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
    Route::delete('/upload/{periode}', [UploadController::class, 'destroy'])
        ->where('periode', '[0-9A-Za-z\-]+')
        ->name('upload.destroy');

    // ---- Halaman sementara. Akan diganti controller asli di tahap berikutnya ----
    Route::view('/laporan', 'placeholder', ['judul' => 'Laporan', 'tahap' => 'D'])->name('laporan');
    Route::view('/pengaturan', 'placeholder', ['judul' => 'Pengaturan', 'tahap' => 'E'])->name('pengaturan');
    Route::view('/profil', 'placeholder', ['judul' => 'Profil', 'tahap' => 'E'])->name('profil');
    Route::view('/admin/approval', 'placeholder', ['judul' => 'Approval User', 'tahap' => 'E'])->name('admin.approval');
});
