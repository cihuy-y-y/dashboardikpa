<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') Dashboard IKPA</title>
<link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial, Helvetica, sans-serif;}
body{height:100vh;display:flex;justify-content:center;align-items:center;background:#001F3F;}
.container{width:400px;background:#fff;padding:35px;border-radius:15px;box-shadow:0 15px 30px rgba(0,0,0,.2);}
.container h2{text-align:center;color:#1d4ed8;margin-bottom:25px;}
.input-group{margin-bottom:15px;}
.input-group label{display:block;margin-bottom:6px;font-weight:bold;}
.input-group input{width:100%;padding:12px;border:1px solid #ccc;border-radius:8px;font-size:15px;}
.input-group input:focus{outline:none;border-color:#1d4ed8;}
button{width:100%;padding:12px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:16px;transition:.3s;}
button:hover{background:#1e40af;}
.link{text-align:center;margin-top:18px;}
.link a{text-decoration:none;color:#1d4ed8;font-weight:bold;}
.link a:hover{text-decoration:underline;}
.alert{padding:12px;margin-bottom:15px;border-radius:8px;background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
.alert.success{background-color:#d4edda;color:#155724;border:1px solid #c3e6cb;}
.logo-container{text-align:center;margin-bottom:20px;}
.logo-container img{max-width:120px;height:auto;}
.password-hint{font-size:12px;color:#666;margin-top:5px;}
.password-wrapper{position:relative;}
.password-wrapper input{width:100%;padding-right:45px;}
.toggle-password{position:absolute;right:6px;top:50%;transform:translateY(-50%);width:auto;background:none;color:#666;border:none;border-radius:0;padding:6px;font-size:18px;line-height:1;cursor:pointer;transition:color .2s;}
.toggle-password:hover{background:none;color:#1d4ed8;}
.toggle-password:focus{outline:none;}
</style>
</head>

<body>
<div class="container">
    <div class="logo-container">
        <img src="{{ asset('assets/img/log051.png') }}" alt="Logo KPPN">
    </div>

    @yield('content')
</div>

<script>
// Tombol "lihat password"
document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-target'));
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});
</script>
@stack('scripts')
</body>
</html>
