@extends('layouts.auth')
@section('title', 'Daftar Mahasiswa')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Akun mahasiswa</p><h2>Buat akun baru</h2><p>Pilih nama sesuai daftar akademik. NIM, program studi, dan angkatan akan terisi otomatis.</p></div>
    <form method="POST" action="{{ route('register.store') }}" class="form-stack" data-registration-form>@csrf
        <label class="field"><span>Nama mahasiswa</span><select name="student_registry_id" required data-student-registry><option value="">Pilih nama</option>@foreach($students as $student)<option value="{{ $student->id }}" data-nim="{{ $student->nim }}" data-program="{{ $student->studyProgram->name }}" data-cohort="{{ $student->cohort }}" @selected(old('student_registry_id') == $student->id)>{{ $student->name }}</option>@endforeach</select></label>
        <div class="registration-preview" data-registration-preview>
            <div><small>NIM</small><strong data-registry-nim>Belum dipilih</strong></div>
            <div><small>Program studi</small><strong data-registry-program>Belum dipilih</strong></div>
            <div><small>Angkatan</small><strong data-registry-cohort>2023</strong></div>
        </div>
        <label class="field"><span>Email aktif</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
        <label class="field"><span>Password</span><span class="password-field"><input type="password" name="password" required autocomplete="new-password" data-password-input><button type="button" data-password-toggle aria-label="Tampilkan password">Lihat</button></span></label>
        <p class="field-hint">Minimal 8 karakter, mengandung huruf dan angka.</p>
        <button class="button button-primary button-block" type="submit">Daftar dan verifikasi email</button>
    </form>
    <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('login') }}">Kembali masuk</a></p>
@endsection
