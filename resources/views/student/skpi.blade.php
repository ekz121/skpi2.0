@extends('layouts.app')
@section('title', 'Pengajuan SKPI')
@section('content')
    @php($canReapply = !$skpi || in_array($skpi->status, ['issued', 'rejected', 'withdrawn'], true))
    <div class="page-heading"><p class="section-kicker">Dokumen pendamping ijazah</p><h1>Pengajuan dan unduh SKPI</h1><p>Satu sertifikat tersimpan sudah cukup. Periksa data dokumen sebelum mengirim permintaan.</p></div>
    <div class="eligibility-checklist">
        <article class="check-item {{ $approvedSubmissions->isNotEmpty() ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $approvedSubmissions->isNotEmpty() ? 'check' : 'clock'])</span><div><strong>Minimal satu sertifikat</strong><p>{{ $approvedSubmissions->count() }} sertifikat tersimpan</p></div></article>
        <article class="check-item {{ $profileComplete ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $profileComplete ? 'check' : 'alert'])</span><div><strong>Data dokumen lengkap</strong><p>{{ $profileComplete ? 'Identitas siap dicetak' : 'Lengkapi profil sebelum mengajukan' }}</p></div></article>
        <article class="check-item {{ $approvedSubmissions->isNotEmpty() ? 'done' : '' }}"><span>@include('partials.icon', ['name' => $approvedSubmissions->isNotEmpty() ? 'check' : 'clock'])</span><div><strong>Kegiatan siap dicetak</strong><p>{{ $points }} poin tercatat sebagai informasi bobot</p></div></article>
    </div>
    <div class="content-grid skpi-grid">
        <section class="panel">
            <div class="panel-head"><div><h2>Status penerbitan</h2><p>Dokumen Word dibuat langsung dari template resmi dan data akun.</p></div>@if($skpi)<span class="status status-{{ match($skpi->status) {'issued' => 'approved', 'withdrawn' => 'withdrawn', 'rejected' => 'rejected', 'failed' => 'failed', default => 'pending'} }}">{{ match($skpi->status) {'pending'=>'Diperiksa admin','rejected'=>'Ditolak','issued'=>'Telah terbit','failed'=>'Dokumen gagal dibuat','withdrawn'=>'Ditarik',default=>'Diperiksa admin'} }}</span>@endif</div>
            @if($skpi?->status === 'issued')
                <div class="issued-card"><span>@include('partials.icon', ['name' => 'document'])</span><div><small>Nomor dokumen</small><strong>{{ $skpi->document_number }}</strong><p>Terbit {{ $skpi->issued_at->translatedFormat('d F Y, H:i') }}</p></div></div>
                <div class="document-downloads"><a href="{{ route('student.skpi.download', $skpi) }}" class="button button-primary">@include('partials.icon', ['name' => 'download']) Unduh Word</a></div>
                <div class="reapply-section"><h3>Perlu SKPI baru?</h3><p>Dokumen lama tetap tersimpan. Pengajuan berikutnya akan mendapat nomor dokumen baru setelah disetujui admin.</p>@include('student.partials.skpi-request-form')</div>
            @elseif($skpi && !$canReapply)
                <div class="status-message"><strong>{{ $skpi->status === 'failed' ? 'Pembuatan dokumen akan diulang admin' : 'Permintaan sedang diperiksa' }}</strong><p>Admin akan memeriksa data lalu memberi keputusan.</p></div>
            @else
                @if($skpi)
                    <div class="status-message {{ $skpi->status === 'withdrawn' ? 'status-message-danger' : '' }}"><strong>{{ $skpi->status === 'withdrawn' ? 'SKPI lama telah ditarik' : 'Permintaan belum disetujui' }}</strong><p>{{ $skpi->status === 'withdrawn' ? 'Alasan: '.$skpi->admin_note.'. Dokumen lama tidak berlaku dan Anda dapat mengajukan SKPI baru.' : 'Periksa kembali kelengkapan profil dan sertifikat sebelum mengajukan ulang.' }}</p></div>
                @else
                    <div class="process-list"><div class="active"><span>1</span><p><strong>Lengkapi data kelahiran</strong><small>Data akademik lainnya sudah terisi otomatis</small></p></div><div><span>2</span><p><strong>Keputusan admin</strong><small>Admin cukup menyetujui atau menolak</small></p></div><div><span>3</span><p><strong>Dokumen terbit</strong><small>Word tersedia di akun</small></p></div></div>
                @endif
                @include('student.partials.skpi-request-form')
            @endif
            @php($previousIssued = $skpi ? $issuedSkpis->reject(fn($item) => $item->is($skpi)) : $issuedSkpis)
            @if($previousIssued->isNotEmpty())
                <div class="document-history"><h3>Dokumen terbit sebelumnya</h3>@foreach($previousIssued as $issued)<div><span><strong>{{ $issued->document_number }}</strong><small>{{ $issued->issued_at?->translatedFormat('d F Y, H:i') }}</small></span><a href="{{ route('student.skpi.download', $issued) }}" class="button button-secondary">Unduh Word</a></div>@endforeach</div>
            @endif
        </section>
        <aside class="panel">
            <div class="panel-head"><div><h2>Data yang akan dicetak</h2><p>Ringkasan dari profil dan sertifikat tersimpan</p></div></div>
            <dl class="detail-list"><div><dt>Nama</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>NIM</dt><dd>{{ auth()->user()->profile->nim }}</dd></div><div><dt>Program studi</dt><dd>{{ auth()->user()->profile->studyProgram->name }}</dd></div><div><dt>Total bobot</dt><dd>{{ $points }} poin</dd></div></dl>
            <a href="{{ route('student.profile') }}" class="button button-secondary button-block">Lihat data akademik</a>
        </aside>
    </div>
@endsection
