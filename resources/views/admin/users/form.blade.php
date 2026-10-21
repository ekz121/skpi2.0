@extends('layouts.app')
@section('title', 'Ubah User')
@section('content')
@php
    $profile = $user->profile;
    $selectedRole = old('role', $user->role ?: 'student');
    $selectedProgram = old('study_program_id', $user->study_program_id ?: $profile?->study_program_id);
    $selectedFaculty = old('faculty_id', $user->faculty_id ?: $user->studyProgram?->faculty_id ?: $profile?->studyProgram?->faculty_id);
@endphp
<div class="page-heading"><p class="section-kicker">Akun mandiri</p><h1>Ubah user</h1><p>Atur identitas, role, unit kerja, dan status akses akun.</p></div>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel form-panel form-stack" novalidate data-validate-form data-user-role-form>
    @csrf @method('PUT')
    <div class="form-grid two">
        <label class="field"><span>Nama lengkap <b class="required-mark">*</b></span><input type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="150" required><x-field-error name="name" client /></label>
        <label class="field"><span>Email <b class="required-mark">*</b></span><input type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="190" required autocomplete="email"><x-field-error name="email" client /></label>
        <label class="field"><span>Role <b class="required-mark">*</b></span><select name="role" required data-role-select>@foreach($roles as $value=>$label)<option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>@endforeach</select><x-field-error name="role" client /></label>
        <label class="field" data-faculty-field><span>Fakultas <small data-faculty-optional>(opsional)</small><b class="required-mark" data-faculty-required hidden>*</b></span><select name="faculty_id" data-faculty-select><option value="">Pilih fakultas</option>@foreach($faculties as $faculty)<option value="{{ $faculty->id }}" @selected((string)$selectedFaculty === (string)$faculty->id)>{{ $faculty->name }}</option>@endforeach</select><x-field-error name="faculty_id" client /></label>
        <label class="field" data-program-field><span>Program studi <b class="required-mark">*</b></span><select name="study_program_id" data-program-select><option value="">Pilih program studi</option>@foreach($studyPrograms as $program)<option value="{{ $program->id }}" data-faculty="{{ $program->faculty_id }}" @selected((string)$selectedProgram === (string)$program->id)>{{ $program->code }} · {{ $program->name }}</option>@endforeach</select><x-field-error name="study_program_id" client /></label>
    </div>
    <fieldset class="form-section" data-student-fields>
        <legend>Data mahasiswa</legend>
        <div class="form-grid two">
            <label class="field"><span>NIM <b class="required-mark">*</b></span><input type="text" name="nim" value="{{ old('nim', $profile?->nim) }}" maxlength="30"><x-field-error name="nim" client /></label>
            <label class="field"><span>Angkatan <b class="required-mark">*</b></span><input type="number" name="cohort" value="{{ old('cohort', $profile?->cohort ?: 2023) }}" min="2000" max="{{ now()->year + 1 }}"><x-field-error name="cohort" client /></label>
        </div>
    </fieldset>
    <div class="choice-stack">
        <label class="check-row"><input type="hidden" name="email_verified" value="0"><input type="checkbox" name="email_verified" value="1" @checked(old('email_verified', (bool) $user->email_verified_at))><span><strong>Email terverifikasi</strong><small>Akun dapat masuk tanpa menunggu verifikasi email.</small></span></label>
        <label class="check-row"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))><span><strong>Akun aktif</strong><small>Akun nonaktif tidak dapat masuk ke sistem.</small></span></label>
    </div>
    <div class="form-actions"><a href="{{ route('admin.users.show', $user) }}" class="button button-quiet">Batal</a><button class="button button-primary" type="submit">Simpan perubahan</button></div>
</form>
@endsection
