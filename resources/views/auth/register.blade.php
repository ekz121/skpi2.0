@extends('layouts.auth')
@section('title', 'Daftar Mahasiswa')
@section('content')
    @php($selectedStudent = $students->whereNull('user_id')->firstWhere('id', (int) old('student_registry_id')))
    <div class="auth-heading"><p class="section-kicker">Akun mahasiswa</p><h2>Buat akun baru</h2><p>Cari nama sesuai daftar akademik. NIM, program studi, dan angkatan akan terisi otomatis.</p></div>
    @if($students->isEmpty())
        <x-notification type="warning" title="Daftar mahasiswa belum tersedia" :messages="['Data registrasi mahasiswa belum dimuat ke database. Hubungi admin.']" :dismissible="false" />
    @endif
    <form method="POST" action="{{ route('register.store') }}" class="form-stack" data-registration-form data-validate-form novalidate>@csrf
        <div class="field student-combobox" data-student-combobox>
            <label for="student-name-search">Nama mahasiswa</label>
            <div class="student-search-control">
                @include('partials.icon', ['name' => 'search'])
                <input id="student-name-search" type="search" value="{{ $selectedStudent?->name }}" placeholder="Ketik nama atau NIM" autocomplete="off" autocapitalize="words" inputmode="search" spellcheck="false" required role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="student-name-options" aria-describedby="student-search-help{{ $errors->has('student_registry_id') ? ' student_registry_id-error' : '' }}" data-student-search @class(['is-invalid' => $errors->has('student_registry_id')]) @if($errors->has('student_registry_id')) aria-invalid="true" @endif>
            </div>
            <input type="hidden" name="student_registry_id" value="{{ old('student_registry_id') }}" data-student-registry-id>
            <div id="student-name-options" class="student-options" role="listbox" aria-label="Hasil pencarian nama mahasiswa" hidden data-student-options>
                @foreach($students as $student)
                    <button id="student-option-{{ $student->id }}" @class(['student-option', 'student-option-unavailable' => $student->user_id]) type="button" role="option" aria-selected="false" @if($student->user_id) aria-disabled="true" @endif data-student-option data-available="{{ $student->user_id ? 'false' : 'true' }}" data-id="{{ $student->id }}" data-name="{{ $student->name }}" data-nim="{{ $student->nim }}" data-program="{{ $student->studyProgram->name }}" data-cohort="{{ $student->cohort }}">
                        <strong>{{ $student->name }}</strong>
                        <span>{{ $student->nim }} · {{ $student->studyProgram->name }}</span>
                        @if($student->user_id)<em>Sudah memiliki akun</em>@endif
                    </button>
                @endforeach
                <p class="student-search-empty" hidden data-student-empty>Nama atau NIM tidak ditemukan. Periksa ejaan atau hubungi admin.</p>
            </div>
            <small id="student-search-help">Sentuh kolom lalu ketik sebagian nama atau NIM. Pencarian tidak membedakan huruf besar dan kecil.</small>
            <x-field-error name="student_registry_id" client />
            <div class="student-account-status" hidden data-student-account-status>
                <strong>Nama ini sudah memiliki akun.</strong>
                <span>Silakan <a href="{{ route('login') }}">masuk</a> atau gunakan <a href="{{ route('password.request') }}">lupa password</a>.</span>
            </div>
            <span class="sr-only" role="status" aria-live="polite" data-student-search-status></span>
        </div>
        <div class="registration-preview" data-registration-preview>
            <div><small>NIM</small><strong data-registry-nim>{{ $selectedStudent?->nim ?? 'Belum dipilih' }}</strong></div>
            <div><small>Program studi</small><strong data-registry-program>{{ $selectedStudent?->studyProgram->name ?? 'Belum dipilih' }}</strong></div>
            <div><small>Angkatan</small><strong data-registry-cohort>{{ $selectedStudent?->cohort ?? '2023' }}</strong></div>
        </div>
        <label class="field"><span>Email aktif</span><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" @class(['is-invalid' => $errors->has('email')]) @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif><x-field-error name="email" client /></label>
        <label class="field"><span>Kata sandi</span><span class="password-field"><input type="password" name="password" required autocomplete="new-password" data-password-input @class(['is-invalid' => $errors->has('password')]) @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false"><span data-password-icon-show>@include('partials.icon', ['name' => 'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon', ['name' => 'eye-off'])</span></button></span><x-field-error name="password" client /></label>
        <p class="field-hint">Minimal 8 karakter, mengandung huruf dan angka.</p>
        <button class="button button-primary button-block" type="submit" @disabled($students->whereNull('user_id')->isEmpty())>Daftar dan verifikasi email</button>
    </form>
    <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('login') }}">Kembali masuk</a></p>
@endsection
