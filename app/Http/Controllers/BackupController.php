<?php

namespace App\Http\Controllers;

use App\Support\DatabaseBackup;
use Illuminate\Http\Request;

/**
 * Backup / restore database. Semua route di sini hanya untuk ADMIN.
 * File backup disimpan di storage/app/private/backups (tidak bisa dibuka langsung dari browser).
 */
class BackupController extends Controller
{
    public function buat()
    {
        try {
            $nama = DatabaseBackup::buat();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('pengaturan')->with('error_backup', 'Gagal membuat backup database.');
        }

        return redirect()->route('pengaturan')
            ->with('success', 'Backup database berhasil. File backup tersimpan di storage/app/private/backups.')
            ->with('backup_baru', $nama);
    }

    public function unduh(string $nama)
    {
        $path = DatabaseBackup::lokasiAman($nama);
        abort_if($path === null, 404);

        return response()->download($path, $nama);
    }

    public function hapus(string $nama)
    {
        $path = DatabaseBackup::lokasiAman($nama);

        if ($path === null) {
            return redirect()->route('pengaturan')->with('error_backup', 'File backup tidak ditemukan.');
        }

        if (@unlink($path)) {
            return redirect()->route('pengaturan')->with('pesan_backup', "Backup \"$nama\" berhasil dihapus.");
        }

        return redirect()->route('pengaturan')->with('error_backup', "Gagal menghapus file \"$nama\".");
    }

    public function pulihkan(Request $request)
    {
        $file = $request->file('database_file');

        if (! $file || ! $file->isValid()) {
            return redirect()->route('pengaturan')->with('error', 'File backup tidak ditemukan atau gagal diunggah.');
        }

        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return redirect()->route('pengaturan')->with('error', 'File restore harus berekstensi .sql.');
        }

        try {
            DatabaseBackup::pulihkan($file->getRealPath());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('pengaturan')->with('error', 'Gagal restore database: ' . $e->getMessage());
        }

        return redirect()->route('pengaturan')
            ->with('success', 'Restore database berhasil. Silakan muat ulang halaman untuk melihat perubahan.');
    }
}
