@extends('layouts.app')
@section('title', 'Dashboard Mahasiswa')
@section('context', 'Portal Mahasiswa')
@section('content')
    @php($activityCount = (int) ($counts['approved'] ?? 0))
    <div class="page-heading heading-with-action">
        <div><p class="section-kicker">Ringkasan kegiatan mahasiswa</p><h1>Halo, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Sertifikat yang diunggah langsung tersimpan dalam rekap dan dapat digunakan untuk mengajukan SKPI.</p></div>
        <a href="{{ route('student.submissions.create') }}" class="button button-primary">@include('partials.icon', ['name' => 'upload']) Unggah sertifikat</a>
    </div>

    <section class="progress-overview" aria-labelledby="activity-summary-title">
        <div class="progress-copy">
            <span class="metric-label" id="activity-summary-title">Kegiatan tersimpan</span>
            <div class="point-total"><strong>{{ $activityCount }}</strong><span>sertifikat</span></div>
            <div class="progress-track large"><span style="width: {{ $activityCount > 0 ? 100 : 0 }}%"></span></div>
            <p>{{ $activityCount > 0 ? 'Syarat unggahan terpenuhi. Isi tempat dan tanggal lahir saat mengajukan SKPI.' : 'Unggah satu sertifikat untuk membuka pengajuan SKPI.' }}</p>
        </div>
        <div class="eligibility-panel {{ $activityCount > 0 ? 'complete' : '' }}">
            <span class="eligibility-icon">@include('partials.icon', ['name' => $activityCount > 0 ? 'check' : 'clock'])</span>
            <span><small>Status pengajuan</small><strong>{{ $activityCount > 0 ? 'Siap mengajukan SKPI' : 'Belum ada sertifikat' }}</strong></span>
            <a href="{{ route('student.skpi') }}" aria-label="Lihat pengajuan SKPI">@include('partials.icon', ['name' => 'chevron'])</a>
        </div>
    </section>

    <div class="metrics-row">
        <article class="metric-card"><span class="metric-icon primary">@include('partials.icon', ['name' => 'certificate'])</span><div><small>Sertifikat tersimpan</small><strong>{{ $activityCount }}</strong><span>langsung masuk rekap</span></div></article>
        <article class="metric-card"><span class="metric-icon green">@include('partials.icon', ['name' => 'approved'])</span><div><small>Total poin</small><strong>{{ $approvedPoints }}</strong><span>informasi bobot kegiatan</span></div></article>
        <article class="metric-card"><span class="metric-icon amber">@include('partials.icon', ['name' => 'chart'])</span><div><small>Kategori terisi</small><strong>{{ $categoryPoints->count() }}</strong><span>kategori kegiatan</span></div></article>
        <article class="metric-card"><span class="metric-icon neutral">@include('partials.icon', ['name' => 'document'])</span><div><small>Status SKPI</small><strong class="metric-word">{{ $skpi ? match($skpi->status) {'pending' => 'Diperiksa', 'rejected' => 'Ditolak', 'issued' => 'Terbit', 'failed' => 'Gagal', default => 'Belum diajukan'} : 'Belum diajukan' }}</strong><span>dokumen pendamping</span></div></article>
    </div>

    <div class="dashboard-grid">
        <section class="panel panel-table">
            <div class="panel-head"><div><h2>Sertifikat terbaru</h2><p>Data yang sudah masuk ke akun Anda</p></div><a href="{{ route('student.submissions.index') }}" class="text-link">Lihat semua</a></div>
            @if($recentSubmissions->isEmpty())
                <div class="empty-state">@include('partials.icon', ['name' => 'file'])<h3>Belum ada sertifikat</h3><p>Unggah sertifikat pertama untuk memulai rekap kegiatan.</p><a href="{{ route('student.submissions.create') }}" class="button button-secondary">Unggah sertifikat</a></div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Kegiatan</th><th>Kategori</th><th>Poin</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach($recentSubmissions as $submission)
                    <tr><td><strong>{{ $submission->activity_name }}</strong><small>{{ $submission->started_at->translatedFormat('d M Y') }}</small></td><td>{{ $submission->rule->category }}</td><td><strong>{{ $submission->approved_points }}</strong></td><td><span class="status status-approved">Tersimpan</span></td><td><a class="table-action" href="{{ route('student.submissions.show', $submission) }}" aria-label="Lihat {{ $submission->activity_name }}">@include('partials.icon', ['name' => 'chevron'])</a></td></tr>
                @endforeach
                </tbody></table></div>
            @endif
        </section>

        <aside class="panel category-panel">
            <div class="panel-head"><div><h2>Komposisi poin</h2><p>Informasi bobot per kategori</p></div></div>
            @forelse($categoryPoints as $category => $points)
                <div class="category-row"><div><span>{{ $category }}</span><strong>{{ $points }} poin</strong></div><div class="progress-track"><span style="width: {{ $approvedPoints ? ($points / $approvedPoints) * 100 : 0 }}%"></span></div></div>
            @empty
                <div class="empty-state compact"><p>Komposisi muncul setelah sertifikat pertama tersimpan.</p></div>
            @endforelse
            <a href="{{ route('student.recap') }}" class="button button-secondary button-block">Buka rekap lengkap</a>
        </aside>
    </div>
@endsection
