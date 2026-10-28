@extends('layouts.auth')
@section('title', 'Register')

@section('content')
    <h2>REGISTER Dashboard IKPA</h2>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('register.proses') }}">
        @csrf

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan Username" required>
            <div class="password-hint">Minimal 3 karakter</div>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan Email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Masukkan Password" required>
                <button type="button" class="toggle-password" data-target="password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div class="password-hint">Minimal 6 karakter</div>
        </div>

        <div class="input-group">
            <label>Konfirmasi Password</label>
            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi Password" required>
                <button type="button" class="toggle-password" data-target="password_confirmation" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div class="password-hint" id="confirm_hint">Harus sama dengan password di atas</div>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        Sudah punya akun?
        <a href="{{ route('login') }}">Login</a>
    </div>
@endsection

@push('scripts')
<script>
// Validasi instan: cek apakah konfirmasi password sudah cocok
var passwordInput = document.getElementById('password');
var confirmInput = document.getElementById('password_confirmation');
var confirmHint = document.getElementById('confirm_hint');

function cekKecocokanPassword() {
    if (confirmInput.value === '') {
        confirmHint.textContent = 'Harus sama dengan password di atas';
        confirmHint.style.color = '#666';
        confirmInput.setCustomValidity('');
        return;
    }
    if (passwordInput.value === confirmInput.value) {
        confirmHint.textContent = 'Password cocok';
        confirmHint.style.color = '#155724';
        confirmInput.setCustomValidity('');
    } else {
        confirmHint.textContent = 'Password tidak cocok';
        confirmHint.style.color = '#721c24';
        confirmInput.setCustomValidity('Password tidak cocok');
    }
}
passwordInput.addEventListener('input', cekKecocokanPassword);
confirmInput.addEventListener('input', cekKecocokanPassword);
</script>
@endpush
