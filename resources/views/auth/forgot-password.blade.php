@extends('layouts.auth')
@section('title', 'Lupa Password')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Pemulihan akun</p><h2>Atur ulang password</h2><p>Masukkan email akun. Jika terdaftar, sistem akan mengirim tautan yang hanya dapat digunakan sekali.</p></div>
    <form method="POST" action="{{ route('password.email') }}" class="form-stack">@csrf
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <button class="button button-primary button-block" type="submit">Kirim tautan reset</button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
@endsection
