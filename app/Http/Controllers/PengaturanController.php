<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Support\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    // Pengganti pengaturan.php (bagian akun + daftar backup)
    public function index()
    {
        $user = auth()->user();

        return view('pengaturan', [
            'user'        => $user,
            // Daftar backup hanya dimuat untuk admin
            'backupFiles' => $user->isAdmin() ? DatabaseBackup::daftar() : [],
        ]);
    }

    public function updateUsername(Request $request)
    {
        $user = $request->user();
        $request->merge(['username' => trim((string) $request->input('username'))]);

        $request->validate([
            'username' => ['required', 'min:4', 'max:50'],
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'username.min'      => 'Username minimal 4 karakter.',
        ]);

        if (Pengguna::where('username', $request->username)->where('id', '!=', $user->id)->exists()) {
            return back()->withInput()->withErrors(['username' => 'Username sudah digunakan.']);
        }

        $user->update(['username' => $request->username]);

        return redirect()->route('pengaturan')->with('success', 'Username berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required'],
            'new_password'     => ['required', 'min:6'],
            'confirm_password' => ['required', 'same:new_password'],
        ], [
            'current_password.required' => 'Semua field password harus diisi.',
            'new_password.required'     => 'Semua field password harus diisi.',
            'confirm_password.required' => 'Semua field password harus diisi.',
            'new_password.min'          => 'Password baru minimal 6 karakter.',
            'confirm_password.same'     => 'Password baru dan konfirmasi tidak sama.',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        $user->password = $request->new_password;   // di-hash otomatis oleh model
        $user->save();

        return redirect()->route('pengaturan')->with('success', 'Password berhasil diperbarui.');
    }
}
