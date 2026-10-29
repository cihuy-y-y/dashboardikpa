@extends('layouts.app')
@section('title', 'Pengaturan - ' . config('ikpa.name'))

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="panel-card">
                <h6 class="panel-title">Pengaturan Akun</h6>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- ===== Ganti username ===== --}}
                <h6 class="mb-3">Ganti Username</h6>
                <form method="POST" action="{{ route('pengaturan.username') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Username Baru</label>
                        <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person"></i>
                        Simpan Username
                    </button>
                </form>

                <hr>

                {{-- ===== Ganti password ===== --}}
                <h6 class="mb-3">Ganti Password</h6>
                <form method="POST" action="{{ route('pengaturan.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" id="current_password" placeholder="Masukkan password saat ini" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Masukkan password baru" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Ulangi password baru" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="panel-card">
                <h6 class="panel-title">Informasi Keamanan</h6>
                <p class="mb-3">Di halaman ini Anda dapat mengganti password akun Anda untuk menjaga keamanan akses dashboard. Pastikan password baru terdiri dari minimal 6 karakter.</p>

                <dl class="row">
                    <dt class="col-sm-4">Username</dt>
                    <dd class="col-sm-8">{{ $user->username }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $user->email ?: '-' }}</dd>

                    <dt class="col-sm-4">Kekuatan Rekomendasi</dt>
                    <dd class="col-sm-8">Gunakan kombinasi huruf, angka, dan simbol</dd>

                    <dt class="col-sm-4">Catatan</dt>
                    <dd class="col-sm-8">Password tidak ditampilkan demi keamanan.</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- ===== Backup & Restore: hanya untuk admin ===== --}}
    @if ($user->isAdmin())
        <div class="row g-3 mt-3">
            <div class="col-12 col-xl-6">
                <div class="panel-card">
                    <h6 class="panel-title">Backup Database</h6>
                    <p class="mb-3">Buat salinan file SQL dari database saat ini. File backup disimpan di folder <code>storage/app/private/backups</code>.</p>

                    @if (session('pesan_backup'))
                        <div class="alert alert-success py-2">{{ session('pesan_backup') }}</div>
                    @endif
                    @if (session('error_backup'))
                        <div class="alert alert-danger py-2">{{ session('error_backup') }}</div>
                    @endif

                    <form method="POST" action="{{ route('backup.buat') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Backup Sekarang</button>
                    </form>

                    @if (session('backup_baru'))
                        <div class="mt-3">
                            <a href="{{ route('backup.unduh', session('backup_baru')) }}" class="btn btn-outline-primary">Download Backup</a>
                        </div>
                    @endif

                    @if (!empty($backupFiles))
                        <div class="mt-4">
                            <h6 class="mb-2">Backup Terakhir</h6>
                            <ul class="list-group">
                                @foreach ($backupFiles as $fileName)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        {{ $fileName }}
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('backup.unduh', $fileName) }}" class="btn btn-sm btn-outline-secondary">Unduh</a>
                                            <form method="POST" action="{{ route('backup.hapus', $fileName) }}"
                                                  onsubmit="return confirm('Hapus backup ini? Tindakan tidak bisa dibatalkan.');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="panel-card">
                    <h6 class="panel-title">Restore Database</h6>
                    <p class="mb-3">Unggah file SQL hasil backup untuk mengembalikan data database. <strong>Data yang ada saat ini akan ditimpa.</strong></p>
                    <form method="POST" action="{{ route('backup.pulihkan') }}" enctype="multipart/form-data"
                          onsubmit="return confirm('Restore akan menimpa data database saat ini. Lanjutkan?');">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">File Backup (.sql)</label>
                            <input type="file" class="form-control" name="database_file" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Restore Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-target'));
        var icon = btn.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});
</script>
@endpush
