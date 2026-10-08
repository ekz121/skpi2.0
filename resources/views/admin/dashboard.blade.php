@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('context', 'Administrasi SKPI')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Permintaan mahasiswa</p><h1>Selamat datang, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Periksa permintaan SKPI, berikan keputusan, lalu unduh dokumen Word dan PDF yang sudah terbit.</p></div><a href="{{ route('admin.skpi.index', ['status' => 'pending']) }}" class="button button-primary">Buka permintaan SKPI</a></div>
    <div class="metrics-row admin-metrics">
        <article class="metric-card"><span class="metric-icon primary">@include('partials.icon', ['name' => 'pending'])</span><div><small>Menunggu keputusan</small><strong>{{ $pendingCount }}</strong><span>permintaan SKPI</span></div></article>
        <article class="metric-card"><span class="metric-icon green">@include('partials.icon', ['name' => 'approved'])</span><div><small>Sudah terbit</small><strong>{{ $issuedCount }}</strong><span>Word dan PDF tersedia</span></div></article>
        <article class="metric-card"><span class="metric-icon amber">@include('partials.icon', ['name' => 'alert'])</span><div><small>Ditolak</small><strong>{{ $rejectedCount }}</strong><span>belum diterbitkan</span></div></article>
        <article class="metric-card"><span class="metric-icon neutral">@include('partials.icon', ['name' => 'document'])</span><div><small>Gagal dibuat</small><strong>{{ $failedCount }}</strong><span>dapat diproses ulang</span></div></article>
    </div>
    <div class="dashboard-grid admin-dashboard-grid">
        <section class="panel panel-table"><div class="panel-head"><div><h2>Permintaan terbaru</h2><p>Urut dari permintaan yang paling lama menunggu</p></div><a href="{{ route('admin.skpi.index') }}" class="text-link">Kelola semua</a></div>
            @if($recentRequests->isEmpty())<div class="empty-state">@include('partials.icon', ['name' => 'check'])<h3>Tidak ada permintaan menunggu</h3><p>Permintaan baru dari mahasiswa akan tampil di sini.</p></div>@else
            <div class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Program studi</th><th>Diajukan</th><th>Status</th><th></th></tr></thead><tbody>@foreach($recentRequests as $item)<tr><td><strong>{{ $item->user->name }}</strong><small>{{ $item->user->profile?->nim }}</small></td><td>{{ $item->user->profile?->studyProgram?->name }}</td><td>{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td><td><span class="status status-pending">Menunggu</span></td><td><a href="{{ route('admin.skpi.show', $item) }}" class="table-action" aria-label="Periksa SKPI {{ $item->user->name }}">@include('partials.icon', ['name' => 'chevron'])</a></td></tr>@endforeach</tbody></table></div>@endif
        </section>
        <aside class="panel category-panel"><div class="panel-head"><div><h2>Status dokumen</h2><p>Distribusi seluruh permintaan</p></div></div>@php($statusTotal = $statusCounts->sum())@foreach(['pending'=>'Menunggu','issued'=>'Terbit','rejected'=>'Ditolak','failed'=>'Gagal'] as $status=>$label)<div class="category-row"><div><span>{{ $label }}</span><strong>{{ $statusCounts[$status] ?? 0 }}</strong></div><div class="progress-track status-track {{ $status }}"><span style="width: {{ $statusTotal ? (($statusCounts[$status] ?? 0) / $statusTotal) * 100 : 0 }}%"></span></div></div>@endforeach</aside>
    </div>
@endsection
