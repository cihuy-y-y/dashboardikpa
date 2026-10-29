@extends('layouts.app')
@section('title', 'Profil - ' . config('ikpa.name'))

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="panel-card">
                <h6 class="panel-title">Profil Saya</h6>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('profil.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Daftar</label>
                        <input type="text" class="form-control"
                            value="{{ $user->tanggal_daftar ? $user->tanggal_daftar->locale('id')->translatedFormat('d F Y H:i') : '-' }}" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="panel-card">
                <h6 class="panel-title">Informasi Akun</h6>
                <p class="mb-2">Gunakan halaman Profil untuk memperbarui username dan alamat email Anda. Jika Anda ingin mengganti password, buka <a href="{{ route('pengaturan') }}">Pengaturan</a>.</p>
                <dl class="row">
                    <dt class="col-sm-4">Nama Akun</dt>
                    <dd class="col-sm-8">{{ $user->username }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $user->email ?: '-' }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">Aktif</dd>

                    <dt class="col-sm-4">Akses</dt>
                    <dd class="col-sm-8">{{ $user->isAdmin() ? 'Administrator' : 'Pengguna Terdaftar' }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
