<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    // Pengganti profil.php
    public function index()
    {
        return view('profil', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'username' => trim((string) $request->input('username')),
            'email'    => trim((string) $request->input('email')),
        ]);

        $request->validate([
            'username' => ['required', 'max:50'],
            'email'    => ['required', 'email', 'max:100'],
        ], [
            'username.required' => 'Username dan Email tidak boleh kosong.',
            'email.required'    => 'Username dan Email tidak boleh kosong.',
            'email.email'       => 'Format Email tidak valid.',
        ]);

        $dipakaiAkunLain = Pengguna::where('id', '!=', $user->id)
            ->where(function ($q) use ($request) {
                $q->where('username', $request->username)->orWhere('email', $request->email);
            })
            ->exists();

        if ($dipakaiAkunLain) {
            return back()->withInput()->withErrors(['username' => 'Username atau Email sudah digunakan oleh akun lain.']);
        }

        $user->update(['username' => $request->username, 'email' => $request->email]);

        return redirect()->route('profil')->with('success', 'Profil berhasil diperbarui.');
    }
}
