@extends('layouts.auth')
@section('title', 'Login')

@section('content')
    <h2>LOGIN Dashboard IKPA</h2>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.proses') }}">
        @csrf

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan Username" required autofocus>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Masukkan Password" required>
                <button type="button" class="toggle-password" data-target="password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        Belum punya akun?
        <a href="{{ route('register') }}">Register</a>
    </div>
@endsection
