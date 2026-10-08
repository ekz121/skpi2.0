@extends('layouts.auth')
@section('title', 'Daftar Mahasiswa')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Akun mahasiswa</p><h2>Buat akun baru</h2><p>Gunakan identitas akademik yang benar. Satu NIM hanya dapat digunakan untuk satu akun.</p></div>
    <form method="POST" action="{{ route('register.store') }}" class="form-stack">@csrf
        <label class="field"><span>Nama lengkap</span><input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"></label>
        <div class="form-grid two">
            <label class="field"><span>NIM</span><input type="text" name="nim" value="{{ old('nim') }}" required></label>
            <label class="field"><span>Angkatan</span><input type="number" name="cohort" value="{{ old('cohort', now()->year) }}" min="2018" max="{{ now()->year }}" required></label>
        </div>
        <label class="field"><span>Program studi</span><select name="study_program_id" required><option value="">Pilih program studi</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected(old('study_program_id') == $program->id)>{{ $program->name }}</option>@endforeach</select></label>
        <label class="field"><span>Email aktif</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
        <div class="form-grid two">
            <label class="field"><span>Password</span><input type="password" name="password" required autocomplete="new-password"></label>
            <label class="field"><span>Konfirmasi password</span><input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        </div>
        <p class="field-hint">Minimal 8 karakter, mengandung huruf dan angka.</p>
        <button class="button button-primary button-block" type="submit">Daftar dan verifikasi email</button>
    </form>
    <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('login') }}">Kembali masuk</a></p>
@endsection
