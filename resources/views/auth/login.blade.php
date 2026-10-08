@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
    <div class="auth-heading">
        <p class="section-kicker">Akses akun</p>
        <h2>Selamat datang kembali</h2>
        <p>Masuk menggunakan email kampus dan password. Sistem menentukan akses dari role akun Anda.</p>
    </div>
    <form method="POST" action="{{ route('login.attempt') }}" class="form-stack">@csrf
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="nama@polteksi.ac.id"></label>
        <label class="field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password"></label>
        <div class="form-row between">
            <label class="check-field"><input type="checkbox" name="remember" value="1"><span>Ingat saya</span></label>
            <a href="{{ route('password.request') }}" class="text-link">Lupa password?</a>
        </div>
        <button class="button button-primary button-block" type="submit">Masuk ke sistem</button>
    </form>
    <p class="auth-switch">Belum memiliki akun mahasiswa? <a href="{{ route('register') }}">Daftar mahasiswa</a></p>
@endsection
