@extends('layouts.auth')
@section('title', 'Daftar Mahasiswa')
@section('content')
    @php($selectedStudent = $students->firstWhere('id', (int) old('student_registry_id')))
    <div class="auth-heading"><p class="section-kicker">Akun mahasiswa</p><h2>Buat akun baru</h2><p>Cari nama sesuai daftar akademik. NIM, program studi, dan angkatan akan terisi otomatis.</p></div>
    <form method="POST" action="{{ route('register.store') }}" class="form-stack" data-registration-form>@csrf
        <div class="field student-combobox" data-student-combobox>
            <label for="student-name-search">Nama mahasiswa</label>
            <div class="student-search-control">
                @include('partials.icon', ['name' => 'search'])
                <input id="student-name-search" type="search" value="{{ $selectedStudent?->name }}" placeholder="Ketik nama mahasiswa" autocomplete="off" spellcheck="false" required role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="student-name-options" aria-describedby="student-search-help" data-student-search>
            </div>
            <input type="hidden" name="student_registry_id" value="{{ old('student_registry_id') }}" data-student-registry-id>
            <div id="student-name-options" class="student-options" role="listbox" aria-label="Hasil pencarian nama mahasiswa" hidden data-student-options>
                @foreach($students as $student)
                    <button id="student-option-{{ $student->id }}" class="student-option" type="button" role="option" aria-selected="false" data-student-option data-id="{{ $student->id }}" data-name="{{ $student->name }}" data-nim="{{ $student->nim }}" data-program="{{ $student->studyProgram->name }}" data-cohort="{{ $student->cohort }}">
                        <strong>{{ $student->name }}</strong>
                        <span>{{ $student->nim }} · {{ $student->studyProgram->name }}</span>
                    </button>
                @endforeach
                <p class="student-search-empty" hidden data-student-empty>Nama tidak ditemukan. Periksa kembali ejaannya.</p>
            </div>
            <small id="student-search-help">Ketik sebagian nama. Huruf besar dan kecil menghasilkan pencarian yang sama.</small>
            <span class="sr-only" role="status" aria-live="polite" data-student-search-status></span>
        </div>
        <div class="registration-preview" data-registration-preview>
            <div><small>NIM</small><strong data-registry-nim>{{ $selectedStudent?->nim ?? 'Belum dipilih' }}</strong></div>
            <div><small>Program studi</small><strong data-registry-program>{{ $selectedStudent?->studyProgram->name ?? 'Belum dipilih' }}</strong></div>
            <div><small>Angkatan</small><strong data-registry-cohort>{{ $selectedStudent?->cohort ?? '2023' }}</strong></div>
        </div>
        <label class="field"><span>Email aktif</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
        <label class="field"><span>Password</span><span class="password-field"><input type="password" name="password" required autocomplete="new-password" data-password-input><button type="button" data-password-toggle aria-label="Tampilkan password">Lihat</button></span></label>
        <p class="field-hint">Minimal 8 karakter, mengandung huruf dan angka.</p>
        <button class="button button-primary button-block" type="submit">Daftar dan verifikasi email</button>
    </form>
    <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('login') }}">Kembali masuk</a></p>
@endsection
