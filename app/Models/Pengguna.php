<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Model untuk tabel lama `pengguna` (menggantikan tabel `users` bawaan Laravel).
 */
class Pengguna extends Authenticatable
{
    protected $table = 'pengguna';

    // Tabel lama tidak punya kolom created_at / updated_at
    public $timestamps = false;

    protected $fillable = ['username', 'email', 'password', 'status'];

    protected $hidden = ['password'];

    protected $casts = [
        'password'       => 'hashed',   // otomatis di-hash (bcrypt) saat diisi
        'tanggal_daftar' => 'datetime',
        'is_admin'       => 'boolean',
    ];

    // Tabel lama tidak punya kolom remember_token, jadi fitur ini dimatikan
    public function getRememberTokenName()
    {
        return '';
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
