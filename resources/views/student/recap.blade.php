@extends('layouts.app')
@section('title', 'Rekap Poin')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Poin terverifikasi</p><h1>Rekap poin SKEM</h1><p>Hanya pengajuan yang sudah disetujui yang masuk dalam perhitungan.</p></div><div class="total-chip"><span>Total</span><strong>{{ $totalPoints }} poin</strong></div></div>
    <div class="category-summary">
        @forelse($byCategory as $category => $points)<article><span>{{ $category }}</span><strong>{{ $points }}</strong><small>poin disetujui</small></article>@empty<div class="empty-state"><p>Belum ada poin yang disetujui.</p></div>@endforelse
    </div>
    <section class="panel panel-table">
        <div class="panel-head"><div><h2>Rincian kegiatan</h2><p>Sumber perhitungan total poin</p></div></div>
        @if($submissions->isEmpty())
            <div class="empty-state">@include('partials.icon', ['name' => 'chart'])<h3>Belum ada poin</h3><p>Poin akan tampil setelah admin menyetujui pengajuan Anda.</p></div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Kegiatan</th><th>Kategori</th><th>Tanggal</th><th>Disetujui</th></tr></thead><tbody>@foreach($submissions as $submission)<tr><td><strong>{{ $submission->activity_name }}</strong><small>{{ $submission->organizer }}</small></td><td>{{ $submission->rule->subcategory }}</td><td>{{ $submission->verified_at?->translatedFormat('d M Y') }}</td><td><strong>{{ $submission->approved_points }} poin</strong></td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
@endsection
