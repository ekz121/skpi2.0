@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-heading">
    <div><p class="section-kicker">Ruang kerja</p><h1>Halo, {{ auth()->user()->name }}.</h1><p>Akun {{ strtolower(auth()->user()->roleLabel()) }} Anda aktif dan terhubung ke sistem SKPI.</p></div>
</div>
<section class="panel form-panel">
    <h2>Informasi penugasan</h2>
    <dl class="detail-list">
        <div><dt>Role</dt><dd>{{ auth()->user()->roleLabel() }}</dd></div>
        <div><dt>Fakultas</dt><dd>{{ auth()->user()->faculty?->name ?? 'Tidak ditentukan' }}</dd></div>
        <div><dt>Program studi</dt><dd>{{ auth()->user()->studyProgram?->name ?? 'Tidak ditentukan' }}</dd></div>
    </dl>
    <p class="helper-text">Fitur operasional khusus role ini dapat ditambahkan sesuai kebijakan kampus. Saat ini akses mahasiswa dan administrasi tetap dibatasi berdasarkan role.</p>
</section>
@endsection
