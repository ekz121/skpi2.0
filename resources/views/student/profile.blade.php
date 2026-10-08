@extends('layouts.app')
@section('title', 'Profil Mahasiswa')
@section('content')
    <div class="page-heading"><p class="section-kicker">Data sumber dokumen</p><h1>Profil mahasiswa</h1><p>Identitas utama berasal dari registrasi. Lengkapi data yang diperlukan untuk penerbitan SKPI.</p></div>
    <div class="content-grid profile-grid">
        <section class="panel">
            <div class="panel-head"><div><h2>Identitas akademik</h2><p>Perubahan identitas utama memerlukan pemeriksaan pengelola.</p></div><span class="status status-approved">Terverifikasi</span></div>
            <dl class="detail-list"><div><dt>Nama lengkap</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>NIM</dt><dd>{{ $profile->nim }}</dd></div><div><dt>Program studi</dt><dd>{{ $profile->studyProgram->name }}</dd></div><div><dt>Angkatan</dt><dd>{{ $profile->cohort }}</dd></div><div><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></div></dl>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Data untuk SKPI</h2><p>Pastikan sesuai dokumen identitas resmi.</p></div></div>
            <form method="POST" action="{{ route('student.profile.update') }}" class="form-stack">@csrf @method('PUT')
                <label class="field"><span>Tempat lahir</span><input type="text" name="birthplace" value="{{ old('birthplace', $profile->birthplace) }}" required></label>
                <label class="field"><span>Tanggal lahir</span><input type="date" name="birthdate" value="{{ old('birthdate', $profile->birthdate?->format('Y-m-d')) }}" required></label>
                <label class="field"><span>Tahun lulus</span><input type="number" name="graduation_year" value="{{ old('graduation_year', $profile->graduation_year) }}" min="2018" max="{{ now()->year + 1 }}" required></label>
                <label class="field"><span>Nomor ijazah nasional</span><input type="text" name="diploma_number" value="{{ old('diploma_number', $profile->diploma_number) }}" maxlength="100" required></label>
                <label class="field"><span>Gelar akademik</span><input type="text" name="academic_title" value="{{ old('academic_title', $profile->academic_title) }}" maxlength="100" placeholder="Contoh: A.Md.Kom." required></label>
                <div class="read-only-note"><strong>Periksa sebelum menyimpan</strong><p>Data ini dicetak langsung pada dokumen Word dan PDF tanpa diketik ulang oleh admin.</p></div>
                <button class="button button-primary" type="submit">Simpan perubahan</button>
            </form>
        </section>
    </div>
@endsection
