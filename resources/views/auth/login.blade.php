<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('ikpa.name') }}</title>
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

    {{-- Kartu login --}}
    <section class="login-card">
        <div class="login-card__badge">
            @include('auth.partials.perisai')
        </div>

        <h2 class="login-card__title">LOGIN <span>Dashboard IKPA</span></h2>

        @if ($errors->any())
            <div class="alert-login">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.proses') }}">
            @csrf

            <div class="field">
                <label class="field__label" for="username"><i class="bi bi-person-fill"></i> Username</label>
                <div class="field__box">
                    <i class="bi bi-person ico"></i>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           placeholder="Masukkan Username" autocomplete="username" required autofocus>
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="password"><i class="bi bi-lock-fill"></i> Password</label>
                <div class="field__box">
                    <i class="bi bi-lock ico"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan Password" autocomplete="current-password" required>
                    <button type="button" class="toggle-password" data-target="password" tabindex="-1" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash-fill"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <div class="divider">atau</div>

        <p class="register-link">
            Belum punya akun?
            <a href="{{ route('register') }}"><i class="bi bi-person-plus-fill"></i> Register</a>
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
</script>
</body>
</html>
