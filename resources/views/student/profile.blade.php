@extends('layouts.app')
@section('title', 'Profil Mahasiswa')
@section('content')
    <div class="page-heading"><p class="section-kicker">Data sumber dokumen</p><h1>Profil mahasiswa</h1><p>Identitas utama berasal dari registrasi. Lengkapi data yang diperlukan untuk penerbitan SKPI.</p></div>
    <div class="content-grid profile-grid">
        <section class="panel">
            <div class="panel-head"><div><h2>Identitas akademik</h2><p>Data ini berasal dari daftar akademik dan tidak diketik ulang saat registrasi.</p></div><span class="status status-approved">Terverifikasi</span></div>
            <dl class="detail-list"><div><dt>Nama lengkap</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>NIM</dt><dd>{{ $profile->nim }}</dd></div><div><dt>Program studi</dt><dd>{{ $profile->studyProgram->name }}</dd></div><div><dt>Angkatan</dt><dd>{{ $profile->cohort }}</dd></div><div><dt>Tahun lulus</dt><dd>{{ $profile->graduation_year }}</dd></div><div><dt>Nomor ijazah nasional</dt><dd>{{ $profile->diploma_number }}</dd></div><div><dt>Gelar akademik</dt><dd>{{ $profile->academic_title }}</dd></div><div><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></div></dl>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Data kelahiran</h2><p>Mahasiswa hanya perlu melengkapi tempat dan tanggal lahir.</p></div></div>
            <form method="POST" action="{{ route('student.profile.update') }}" class="form-stack">@csrf @method('PUT')
                <label class="field"><span>Tempat lahir</span><input type="text" name="birthplace" value="{{ old('birthplace', $profile->birthplace) }}" required></label>
                <label class="field"><span>Tanggal lahir</span><input type="date" name="birthdate" value="{{ old('birthdate', $profile->birthdate?->format('Y-m-d')) }}" required></label>
                <div class="read-only-note"><strong>Data akademik terkunci</strong><p>Tahun lulus 2026, nomor ijazah, dan gelar diambil otomatis dari daftar akademik.</p></div>
                <button class="button button-primary" type="submit">Simpan perubahan</button>
            </form>
        </section>
    </div>
@endsection
