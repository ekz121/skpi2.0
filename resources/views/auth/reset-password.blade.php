@extends('layouts.auth')
@section('title', 'Password Baru')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Pemulihan akun</p><h2>Buat password baru</h2><p>Gunakan password yang berbeda dari password sebelumnya.</p></div>
    <form method="POST" action="{{ route('password.update') }}" class="form-stack" novalidate data-validate-form>@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email', $email) }}" required @class(['is-invalid' => $errors->has('email')]) @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif><x-field-error name="email" client /></label>
        <label class="field"><span>Kata sandi baru</span><span class="password-field"><input type="password" name="password" required autocomplete="new-password" data-password-input @class(['is-invalid' => $errors->has('password')]) @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi baru" aria-pressed="false"><span data-password-icon-show>@include('partials.icon', ['name' => 'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon', ['name' => 'eye-off'])</span></button></span><x-field-error name="password" client /></label>
        <label class="field"><span>Konfirmasi kata sandi</span><span class="password-field"><input type="password" name="password_confirmation" required autocomplete="new-password" data-password-input @class(['is-invalid' => $errors->has('password_confirmation')]) @if($errors->has('password_confirmation')) aria-invalid="true" aria-describedby="password_confirmation-error" @endif><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false"><span data-password-icon-show>@include('partials.icon', ['name' => 'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon', ['name' => 'eye-off'])</span></button></span><x-field-error name="password_confirmation" client /></label>
        <button class="button button-primary button-block" type="submit">Simpan password baru</button>
    </form>
@endsection
