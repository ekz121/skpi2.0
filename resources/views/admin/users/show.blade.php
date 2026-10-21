@extends('layouts.app')
@section('title', 'Detail User')
@section('content')
<div class="page-heading heading-with-action">
    <div><p class="section-kicker">{{ $user->roleLabel() }}</p><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div>
    <div class="heading-actions"><a href="{{ route('admin.users.edit',$user) }}" class="button button-secondary">Ubah data</a><a href="{{ route('admin.users.index') }}" class="button button-quiet">Kembali</a></div>
</div>
<div class="student-detail-layout">
    <section class="panel"><div class="panel-head"><div><h2>Identitas dan akses</h2><p>Data akun, role, dan unit yang digunakan sistem.</p></div></div>
        <dl class="detail-list">
            <div><dt>Status akun</dt><dd><span class="status {{ $user->is_active ? 'status-approved':'status-rejected' }}">{{ $user->is_active ? 'Aktif':'Nonaktif' }}</span>@unless($user->is_active)<small>Dinonaktifkan {{ $user->deactivated_at?->translatedFormat('d F Y, H:i') }} oleh {{ $user->deactivatedBy?->name ?? 'sistem' }}</small>@endunless</dd></div>
            <div><dt>Role</dt><dd>{{ $user->roleLabel() }}</dd></div>
            <div><dt>Email</dt><dd>{{ $user->email }}<small>{{ $user->email_verified_at ? 'Terverifikasi '.$user->email_verified_at->translatedFormat('d F Y, H:i') : 'Belum terverifikasi' }}</small></dd></div>
            <div><dt>Fakultas</dt><dd>{{ $user->faculty?->name ?? '-' }}</dd></div>
            <div><dt>Program studi</dt><dd>{{ $user->studyProgram?->name ?? $user->profile?->studyProgram?->name ?? '-' }}</dd></div>
            @if($user->isStudent())<div><dt>NIM dan angkatan</dt><dd>{{ $user->profile?->nim ?? '-' }}<small>Angkatan {{ $user->profile?->cohort ?? '-' }}</small></dd></div>@endif
        </dl>
    </section>
    <aside class="panel user-admin-actions">
        <div class="account-state"><small>Data terkait</small><strong>{{ $user->submissions_count + $user->skpi_requests_count + $user->submission_decisions_count }}</strong><span>{{ $user->hasRelatedAcademicData() ? 'Akun hanya dapat dinonaktifkan' : 'Akun dapat dihapus permanen' }}</span></div>
        <div class="admin-action-stack">
            <form method="POST" action="{{ route('admin.users.status',$user) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}"><button class="button {{ $user->is_active ? 'button-secondary':'button-primary' }} button-block" type="submit">{{ $user->is_active ? 'Nonaktifkan akun':'Aktifkan akun' }}</button></form>
            <button class="button button-secondary button-block" type="button" data-dialog-open="reset-password">Reset password</button>
        </div>
    </aside>
</div>
<section class="panel danger-zone"><div><h2>{{ $user->hasRelatedAcademicData() ? 'Nonaktifkan akun':'Hapus akun permanen' }}</h2><p>{{ $user->hasRelatedAcademicData() ? 'Data capaian, verifikasi, dan SKPI akan tetap tersimpan. Akses masuk akun dihentikan.' : 'Akun ini belum memiliki data akademik terkait dan dapat dihapus permanen.' }}</p></div><form method="POST" action="{{ route('admin.users.destroy',$user) }}" data-confirm="{{ $user->hasRelatedAcademicData() ? 'Akun memiliki data terkait dan akan dinonaktifkan. Lanjutkan?' : 'Hapus akun '.$user->name.' secara permanen? Tindakan ini tidak dapat dibatalkan.' }}">@csrf @method('DELETE')<button class="button button-danger" type="submit">{{ $user->hasRelatedAcademicData() ? 'Nonaktifkan':'Hapus permanen' }}</button></form></section>
<dialog class="confirm-dialog" id="reset-password" data-dialog @if($errors->has('password')) data-open-on-load @endif>
    <form method="POST" action="{{ route('admin.users.password',$user) }}" class="dialog-card" novalidate data-validate-form>@csrf @method('PUT')
        <input type="email" name="username" value="{{ $user->email }}" autocomplete="username" tabindex="-1" aria-hidden="true" hidden>
        <div class="dialog-head"><div><p class="section-kicker">Keamanan akun</p><h2>Reset password</h2></div><button class="icon-button" type="button" data-dialog-close aria-label="Tutup dialog">@include('partials.icon',['name'=>'close'])</button></div>
        <p>Buat password baru untuk {{ $user->name }}. Password lama langsung tidak berlaku.</p>
        <label class="field"><span>Password baru <b class="required-mark">*</b></span><span class="password-field"><input type="password" name="password" minlength="8" required autocomplete="new-password" data-password-input><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan password"><span data-password-icon-show>@include('partials.icon',['name'=>'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon',['name'=>'eye-off'])</span></button></span><x-field-error name="password" client /></label>
        <label class="field"><span>Konfirmasi password <b class="required-mark">*</b></span><span class="password-field"><input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password" data-password-input><button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan konfirmasi password"><span data-password-icon-show>@include('partials.icon',['name'=>'eye'])</span><span data-password-icon-hide hidden>@include('partials.icon',['name'=>'eye-off'])</span></button></span></label>
        <div class="dialog-actions"><button class="button button-quiet" type="button" data-dialog-close>Batal</button><button class="button button-primary" type="submit">Simpan password</button></div>
    </form>
</dialog>
@endsection
