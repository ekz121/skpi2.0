@extends('layouts.auth')
@section('title', 'Password Baru')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Pemulihan akun</p><h2>Buat password baru</h2><p>Gunakan password yang berbeda dari password sebelumnya.</p></div>
    <form method="POST" action="{{ route('password.update') }}" class="form-stack">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email', $email) }}" required></label>
        <label class="field"><span>Password baru</span><input type="password" name="password" required autocomplete="new-password"></label>
        <label class="field"><span>Konfirmasi password</span><input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        <button class="button button-primary button-block" type="submit">Simpan password baru</button>
    </form>
@endsection
