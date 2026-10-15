@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
    <div class="auth-heading">
        <p class="section-kicker">Akses akun</p>
        <h2>Selamat datang kembali</h2>
        <p>Masuk menggunakan email kampus dan password. Sistem menentukan akses dari role akun Anda.</p>
    </div>
    <form method="POST" action="{{ route('login.attempt') }}" class="form-stack" novalidate data-validate-form>@csrf
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="nama@polteksi.ac.id" @class(['is-invalid' => $errors->has('email')]) @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif><x-field-error name="email" client /></label>
        <label class="field"><span>Kata sandi</span><span class="password-field"><input type="password" name="password" autocomplete="current-password" required placeholder="Masukkan kata sandi" data-password-input @class(['is-invalid' => $errors->has('password')]) @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false"><span data-password-icon-show>@include('partials.icon', ['name' => 'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon', ['name' => 'eye-off'])</span></button></span><x-field-error name="password" client /></label>
        <div class="form-row between">
            <label class="check-field"><input type="checkbox" name="remember" value="1"><span>Ingat saya</span></label>
            <a href="{{ route('password.request') }}" class="text-link">Lupa password?</a>
        </div>
        <button class="button button-primary button-block" type="submit">Masuk ke sistem</button>
    </form>
    <p class="auth-switch">Belum memiliki akun mahasiswa? <a href="{{ route('register') }}">Daftar mahasiswa</a></p>
@endsection
