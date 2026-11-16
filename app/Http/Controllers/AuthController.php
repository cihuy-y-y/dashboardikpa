<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ---------------------------------------------------------- LOGIN
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        if ($username === '' || $password === '') {
            return $this->gagalLogin('Username dan Password harus diisi!', $username);
        }

        $user = Pengguna::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $this->gagalLogin('Username atau Password salah!', $username);
        }

        if ($user->status === 'pending') {
            return $this->gagalLogin('Akun Anda masih menunggu persetujuan Admin. Silakan tunggu konfirmasi.', $username);
        }

        if ($user->status === 'rejected') {
            return $this->gagalLogin('Pendaftaran akun Anda ditolak oleh Admin. Hubungi Admin untuk info lebih lanjut.', $username);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('beranda');
    }

    private function gagalLogin(string $pesan, string $username)
    {
        return back()->withErrors(['login' => $pesan])->withInput(['username' => $username]);
    }

    // ------------------------------------------------------- REGISTER
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'username' => trim((string) $request->input('username')),
            'email'    => trim((string) $request->input('email')),
        ]);

        $request->validate([
            'username' => ['required', 'min:3', 'max:50', 'unique:pengguna,username'],
            'email'    => ['required', 'email', 'max:100', 'unique:pengguna,email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'username.required' => 'Semua field harus diisi!',
            'email.required'    => 'Semua field harus diisi!',
            'password.required' => 'Semua field harus diisi!',
            'username.min'      => 'Username minimal 3 karakter!',
            'password.min'      => 'Password minimal 6 karakter!',
            'email.email'       => 'Format email tidak valid!',
            'username.unique'   => 'Username atau Email sudah terdaftar!',
            'email.unique'      => 'Username atau Email sudah terdaftar!',
            'password.confirmed' => 'Konfirmasi password tidak cocok!',
        ]);

        Pengguna::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,   // di-hash otomatis oleh model
            'status'   => 'pending',
        ]);

        return redirect()->route('register')->with(
            'success',
            'Register berhasil! Akun Anda menunggu persetujuan Admin sebelum bisa digunakan untuk login.'
        );
    }

    // --------------------------------------------------------- LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
