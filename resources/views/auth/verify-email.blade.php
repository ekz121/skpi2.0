@extends('layouts.auth')
@section('title', 'Verifikasi Email')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Satu langkah lagi</p><h2>Periksa email Anda</h2><p>Kami mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Akun belum dapat digunakan sebelum email terverifikasi.</p></div>
    <div class="verification-note">Buka tautan terbaru pada email. Periksa folder spam jika belum terlihat. Tautan lama tidak berlaku setelah alamat email diubah.</div>
    <div class="auth-action-stack">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button button-primary button-block" type="submit">Kirim ulang tautan</button></form>
        <details class="email-correction">
            <summary>Alamat email salah?</summary>
            <form method="POST" action="{{ route('verification.email.update') }}" class="form-stack" novalidate data-validate-form>
                @csrf @method('PUT')
                <label class="field"><span>Alamat email yang benar</span><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required @class(['is-invalid' => $errors->has('email')])><x-field-error name="email" client /></label>
                <button class="button button-secondary button-block" type="submit">Simpan dan kirim verifikasi</button>
            </form>
        </details>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-quiet button-block" type="submit">Keluar dari akun</button></form>
    </div>
@endsection
