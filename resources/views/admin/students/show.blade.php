@extends('layouts.app')
@section('title', 'Detail Mahasiswa')
@section('content')
    @php($profile = $student->profile)
    <div class="page-heading heading-with-action">
        <div><p class="section-kicker">Akun mahasiswa</p><h1>{{ $student->name }}</h1><p>{{ $profile?->nim }} · {{ $profile?->studyProgram?->name }}</p></div>
        <div class="heading-actions"><a href="{{ route('admin.students.edit', $student) }}" class="button button-secondary">Ubah data</a></div>
    </div>

    <div class="student-detail-layout">
        <section class="panel">
            <div class="panel-head"><div><h2>Identitas dan akun</h2><p>Data yang digunakan untuk akun portal dan dokumen SKPI.</p></div></div>
            <dl class="detail-list">
                <div><dt>Email</dt><dd>{{ $student->email }}<small>{{ $student->email_verified_at ? 'Terverifikasi '.$student->email_verified_at->translatedFormat('d F Y, H:i') : 'Belum terverifikasi' }}</small></dd></div>
                <div><dt>NIM</dt><dd>{{ $profile?->nim ?: '-' }}</dd></div>
                <div><dt>Program studi</dt><dd>{{ $profile?->studyProgram?->name ?: '-' }}<small>{{ $profile?->studyProgram?->code }}</small></dd></div>
                <div><dt>Angkatan dan lulus</dt><dd>{{ $profile?->cohort ?: '-' }} / {{ $profile?->graduation_year ?: '-' }}</dd></div>
                <div><dt>Tempat, tanggal lahir</dt><dd>{{ $profile?->birthplace ?: '-' }}{{ $profile?->birthdate ? ', '.$profile->birthdate->translatedFormat('d F Y') : '' }}</dd></div>
                <div><dt>Nomor ijazah</dt><dd>{{ $profile?->diploma_number ?: '-' }}</dd></div>
                <div><dt>Gelar</dt><dd>{{ $profile?->academic_title ?: '-' }}</dd></div>
            </dl>
        </section>
        <aside class="panel student-admin-summary">
            <div><small>Sertifikat aktif</small><strong>{{ $student->submissions->where('status', 'approved')->count() }}</strong><span>{{ $student->approvedPoints() }} poin dalam rekap</span></div>
            <div><small>Pengajuan SKPI</small><strong>{{ $student->skpiRequests->count() }}</strong><span>{{ $student->skpiRequests->first() ? 'Terakhir: '.ucfirst($student->skpiRequests->first()->status) : 'Belum pernah mengajukan' }}</span></div>
            <div class="admin-action-stack"><a href="{{ route('admin.submissions.index', ['q' => $profile?->nim ?: $student->name]) }}" class="button button-secondary button-block">Lihat sertifikat</a><a href="{{ route('admin.skpi.index', ['q' => $profile?->nim ?: $student->name]) }}" class="button button-secondary button-block">Lihat SKPI</a></div>
        </aside>
    </div>

    <section class="panel danger-zone">
        <div><h2>Hapus akun mahasiswa</h2><p>Menghapus akun juga menghapus sertifikat, permintaan SKPI, notifikasi, dan berkas terkait. Data induk akademik tetap disimpan agar mahasiswa dapat mendaftar ulang.</p></div>
        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" data-confirm="Hapus akun {{ $student->name }} beserta seluruh data transaksinya? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" class="button button-danger">Hapus mahasiswa</button></form>
    </section>
@endsection
