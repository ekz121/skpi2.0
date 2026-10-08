@extends('layouts.app')
@section('title', 'Pengajuan SKPI')
@section('content')
    <div class="page-heading"><p class="section-kicker">Dokumen pendamping ijazah</p><h1>Pengajuan dan unduh SKPI</h1><p>Satu sertifikat tersimpan sudah cukup. Periksa data dokumen sebelum mengirim permintaan.</p></div>
    <div class="eligibility-checklist">
        <article class="check-item {{ $approvedSubmissions->isNotEmpty() ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $approvedSubmissions->isNotEmpty() ? 'check' : 'clock'])</span><div><strong>Minimal satu sertifikat</strong><p>{{ $approvedSubmissions->count() }} sertifikat tersimpan</p></div></article>
        <article class="check-item {{ $profileComplete ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $profileComplete ? 'check' : 'alert'])</span><div><strong>Data dokumen lengkap</strong><p>{{ $profileComplete ? 'Identitas siap dicetak' : 'Lengkapi profil sebelum mengajukan' }}</p></div></article>
        <article class="check-item {{ $approvedSubmissions->isNotEmpty() ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $approvedSubmissions->isNotEmpty() ? 'check' : 'clock'])</span><div><strong>Kegiatan siap dicetak</strong><p>{{ $points }} poin tercatat sebagai informasi bobot</p></div></article>
    </div>
    <div class="content-grid skpi-grid">
        <section class="panel">
            <div class="panel-head"><div><h2>Status penerbitan</h2><p>Dokumen Word dibuat langsung dari template resmi dan data akun.</p></div>@if($skpi)<span class="status status-{{ $skpi->status === 'issued' ? 'approved' : ($skpi->status === 'rejected' ? 'rejected' : 'pending') }}">{{ match($skpi->status) {'pending'=>'Diperiksa admin','rejected'=>'Ditolak','issued'=>'Telah terbit','failed'=>'Dokumen gagal dibuat', default=>'Diperiksa admin'} }}</span>@endif</div>
            @if(!$skpi)
                <div class="process-list"><div class="active"><span>1</span><p><strong>Lengkapi data kelahiran</strong><small>Data akademik lainnya sudah terisi otomatis</small></p></div><div><span>2</span><p><strong>Keputusan admin</strong><small>Admin cukup menyetujui atau menolak</small></p></div><div><span>3</span><p><strong>Dokumen terbit</strong><small>Word tersedia di akun</small></p></div></div>
                <form method="POST" action="{{ route('student.skpi.request') }}" class="form-stack skpi-request-form">@csrf
                    <div class="form-grid two"><label class="field"><span>Tempat lahir</span><input type="text" name="birthplace" value="{{ old('birthplace', auth()->user()->profile->birthplace) }}" maxlength="100" required></label><label class="field"><span>Tanggal lahir</span><input type="date" name="birthdate" value="{{ old('birthdate', auth()->user()->profile->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}" required></label></div>
                    <button class="button button-primary" type="submit" @disabled($approvedSubmissions->isEmpty())>Ajukan SKPI</button>
                </form>
                @if($approvedSubmissions->isEmpty())<p class="field-hint">Unggah sedikitnya satu sertifikat untuk mengaktifkan tombol pengajuan.</p>@endif
            @elseif($skpi->status === 'issued')
                <div class="issued-card"><span>@include('partials.icon', ['name' => 'document'])</span><div><small>Nomor dokumen</small><strong>{{ $skpi->document_number }}</strong><p>Terbit {{ $skpi->issued_at->translatedFormat('d F Y, H:i') }}</p></div></div>
                <div class="document-downloads"><a href="{{ route('student.skpi.download', $skpi) }}" class="button button-primary">@include('partials.icon', ['name' => 'download']) Unduh Word</a></div>
            @else
                <div class="status-message"><strong>{{ $skpi->status === 'rejected' ? 'Permintaan belum disetujui' : ($skpi->status === 'failed' ? 'Pembuatan dokumen akan diulang admin' : 'Permintaan sedang diperiksa') }}</strong><p>{{ $skpi->status === 'rejected' ? 'Periksa kembali kelengkapan profil dan sertifikat sebelum mengajukan ulang.' : 'Admin akan memeriksa data lalu memberi keputusan.' }}</p></div>
                @if($skpi->status === 'rejected')<form method="POST" action="{{ route('student.skpi.request') }}" class="reapply-form form-stack">@csrf<div class="form-grid two"><label class="field"><span>Tempat lahir</span><input type="text" name="birthplace" value="{{ old('birthplace', auth()->user()->profile->birthplace) }}" maxlength="100" required></label><label class="field"><span>Tanggal lahir</span><input type="date" name="birthdate" value="{{ old('birthdate', auth()->user()->profile->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}" required></label></div><button class="button button-primary" type="submit" @disabled($approvedSubmissions->isEmpty())>Ajukan kembali</button></form>@endif
            @endif
        </section>
        <aside class="panel">
            <div class="panel-head"><div><h2>Data yang akan dicetak</h2><p>Ringkasan dari profil dan pengajuan disetujui</p></div></div>
            <dl class="detail-list"><div><dt>Nama</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>NIM</dt><dd>{{ auth()->user()->profile->nim }}</dd></div><div><dt>Program studi</dt><dd>{{ auth()->user()->profile->studyProgram->name }}</dd></div><div><dt>Total bobot</dt><dd>{{ $points }} poin</dd></div></dl>
            <a href="{{ route('student.profile') }}" class="button button-secondary button-block">Lihat data akademik</a>
        </aside>
    </div>
@endsection
