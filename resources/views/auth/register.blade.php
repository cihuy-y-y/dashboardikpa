<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ config('ikpa.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}?v=2">
</head>
<body>
<div class="stage">

    @include('auth.partials.latar')

    {{-- Kartu register --}}
    <section class="login-card login-card--register">
        <div class="login-card__badge">
            @include('auth.partials.perisai')
        </div>

        <h2 class="login-card__title">REGISTER <span>Dashboard IKPA</span></h2>

        @if ($errors->any())
            <div class="alert-login">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="alert-login alert-login--success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('register.proses') }}">
            @csrf

            <div class="field">
                <label class="field__label" for="username"><i class="bi bi-person-fill"></i> Username</label>
                <div class="field__box">
                    <i class="bi bi-person ico"></i>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           placeholder="Username (min. 3 karakter)" autocomplete="username" required>
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="email"><i class="bi bi-envelope-fill"></i> Email</label>
                <div class="field__box">
                    <i class="bi bi-envelope ico"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           placeholder="Masukkan Email" autocomplete="email" required>
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="password"><i class="bi bi-lock-fill"></i> Password</label>
                <div class="field__box">
                    <i class="bi bi-lock ico"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Password (min. 6 karakter)" autocomplete="new-password" required>
                    <button type="button" class="toggle-password" data-target="password" tabindex="-1" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash-fill"></i>
                    </button>
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="password_confirmation">
                    <i class="bi bi-shield-lock-fill"></i> Konfirmasi Password
                    <span class="field__hint" id="confirm_hint"></span>
                </label>
                <div class="field__box">
                    <i class="bi bi-lock ico"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ulangi Password" autocomplete="new-password" required>
                    <button type="button" class="toggle-password" data-target="password_confirmation" tabindex="-1" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash-fill"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-person-plus-fill"></i> Register
            </button>
        </form>

        <div class="divider">atau</div>

        <p class="register-link">
            Sudah punya akun?
            <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Login</a>
        </p>
    </section>

</div>

<script>
// Tombol "lihat password"
document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-target'));
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-fill';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash-fill';
        }
    });
});

// Cek instan: konfirmasi password sudah cocok atau belum
var passwordInput = document.getElementById('password');
var confirmInput = document.getElementById('password_confirmation');
var confirmHint = document.getElementById('confirm_hint');

function cekKecocokanPassword() {
    if (confirmInput.value === '') {
        confirmHint.textContent = '';
        confirmHint.className = 'field__hint';
        confirmInput.setCustomValidity('');
    } else if (passwordInput.value === confirmInput.value) {
        confirmHint.textContent = 'Password cocok';
        confirmHint.className = 'field__hint field__hint--ok';
        confirmInput.setCustomValidity('');
    } else {
        confirmHint.textContent = 'Password tidak cocok';
        confirmHint.className = 'field__hint field__hint--bad';
        confirmInput.setCustomValidity('Password tidak cocok');
    }
}
passwordInput.addEventListener('input', cekKecocokanPassword);
confirmInput.addEventListener('input', cekKecocokanPassword);
</script>
</body>
</html>
