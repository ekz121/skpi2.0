@extends('layouts.app')
@section('title', $student->exists ? 'Ubah Mahasiswa' : 'Tambah Mahasiswa')
@section('content')
    @php($profile = $student->profile)
    <div class="page-heading">
        <p class="section-kicker">{{ $student->exists ? 'Pemutakhiran akun' : 'Akun dari data akademik' }}</p>
        <h1>{{ $student->exists ? 'Ubah data mahasiswa' : 'Tambah mahasiswa' }}</h1>
        <p>{{ $student->exists ? 'Perubahan identitas akademik juga diselaraskan ke data induk pendaftaran.' : 'Pilih mahasiswa yang belum memiliki akun. Identitas akademik akan terisi otomatis.' }}</p>
    </div>

    <form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}" class="panel form-panel form-stack" novalidate data-validate-form>
        @csrf
        @if($student->exists) @method('PUT') @endif

        @if(!$student->exists)
            <label class="field">
                <span>Mahasiswa <b class="required-mark" aria-hidden="true">*</b></span>
                <select name="student_registry_id" required>
                    <option value="">Pilih dari data akademik</option>
                    @foreach($registries as $registry)
                        <option value="{{ $registry->id }}" @selected(old('student_registry_id') == $registry->id)>{{ $registry->name }} · {{ $registry->nim }} · {{ $registry->studyProgram?->code }}</option>
                    @endforeach
                </select>
                <x-field-error name="student_registry_id" client />
            </label>
            @if($registries->isEmpty())
                <x-notification type="warning" title="Tidak ada data tersedia" message="Semua mahasiswa pada data akademik sudah memiliki akun." />
            @endif
        @else
            <div class="form-grid two">
                <label class="field"><span>Nama lengkap <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="name" maxlength="150" required value="{{ old('name', $student->name) }}"><x-field-error name="name" client /></label>
                <label class="field"><span>NIM <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="nim" maxlength="30" required value="{{ old('nim', $profile?->nim) }}"><x-field-error name="nim" client /></label>
                <label class="field"><span>Program studi <b class="required-mark" aria-hidden="true">*</b></span><select name="study_program_id" required><option value="">Pilih program studi</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected(old('study_program_id', $profile?->study_program_id) == $program->id)>{{ $program->name }} ({{ $program->code }})</option>@endforeach</select><x-field-error name="study_program_id" client /></label>
                <label class="field"><span>Angkatan <b class="required-mark" aria-hidden="true">*</b></span><input type="number" name="cohort" min="2000" max="2100" required value="{{ old('cohort', $profile?->cohort) }}"><x-field-error name="cohort" client /></label>
                <label class="field"><span>Tempat lahir <small>(opsional)</small></span><input type="text" name="birthplace" maxlength="100" value="{{ old('birthplace', $profile?->birthplace) }}"><x-field-error name="birthplace" client /></label>
                <label class="field"><span>Tanggal lahir <small>(opsional)</small></span><input type="date" name="birthdate" max="{{ now()->subDay()->toDateString() }}" value="{{ old('birthdate', $profile?->birthdate?->format('Y-m-d')) }}"><x-field-error name="birthdate" client /></label>
                <label class="field"><span>Tahun lulus <b class="required-mark" aria-hidden="true">*</b></span><input type="number" name="graduation_year" min="2000" max="2100" required value="{{ old('graduation_year', $profile?->graduation_year ?: 2026) }}"><x-field-error name="graduation_year" client /></label>
                <label class="field"><span>Nomor ijazah <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="diploma_number" maxlength="100" required value="{{ old('diploma_number', $profile?->diploma_number) }}"><x-field-error name="diploma_number" client /></label>
                <label class="field"><span>Gelar <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="academic_title" maxlength="30" required value="{{ old('academic_title', $profile?->academic_title) }}"><x-field-error name="academic_title" client /></label>
            </div>
        @endif

        <div class="form-grid two">
            <label class="field"><span>Email <b class="required-mark" aria-hidden="true">*</b></span><input type="email" name="email" maxlength="190" autocomplete="email" required value="{{ old('email', $student->email) }}"><x-field-error name="email" client /></label>
            <label class="field">
                <span>Kata sandi @if($student->exists)<small>(opsional)</small>@else<b class="required-mark" aria-hidden="true">*</b>@endif</span>
                <span class="password-field"><input type="password" name="password" data-password-input autocomplete="new-password" minlength="8" @required(!$student->exists)><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false"><span data-password-icon-show>@include('partials.icon', ['name'=>'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon', ['name'=>'eye-off'])</span></button></span>
                <small>Minimal 8 karakter, mengandung huruf dan angka. {{ $student->exists ? 'Kosongkan jika tidak diubah.' : '' }}</small>
                <x-field-error name="password" client />
            </label>
        </div>

        @if($student->exists)
            <label class="check-row"><input type="checkbox" name="email_verified" value="1" @checked(old('email_verified', (bool) $student->email_verified_at))><span><strong>Email terverifikasi</strong><small>Jika dimatikan, mahasiswa harus memverifikasi email sebelum membuka portal.</small></span></label>
        @endif

        <div class="form-actions"><a href="{{ $student->exists ? route('admin.students.show', $student) : route('admin.students') }}" class="button button-quiet">Batal</a><button type="submit" class="button button-primary" @disabled(!$student->exists && $registries->isEmpty())>{{ $student->exists ? 'Simpan perubahan' : 'Buat akun mahasiswa' }}</button></div>
    </form>
@endsection
