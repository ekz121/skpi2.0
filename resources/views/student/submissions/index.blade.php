@extends('layouts.app')
@section('title', 'Riwayat Sertifikat')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Rekap bukti kegiatan</p><h1>Riwayat sertifikat</h1><p>Semua sertifikat yang diunggah langsung tersimpan dan masuk ke rekap kegiatan.</p></div><a href="{{ route('student.submissions.create') }}" class="button button-primary">@include('partials.icon', ['name' => 'upload']) Unggah sertifikat</a></div>
    <form method="GET" class="filter-bar"><label class="select-filter">@include('partials.icon', ['name' => 'filter'])<select name="status" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['draft'=>'Draf','pending'=>'Menunggu','revision'=>'Perlu revisi','approved'=>'Disetujui','rejected'=>'Ditolak'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>@if(request('status'))<a href="{{ route('student.submissions.index') }}" class="text-link">Hapus filter</a>@endif</form>
    <section class="panel panel-table">
        @if($submissions->isEmpty())
            <div class="empty-state">@include('partials.icon', ['name' => 'history'])<h3>{{ request('status') ? 'Tidak ada hasil untuk filter ini' : 'Belum ada pengajuan' }}</h3><p>{{ request('status') ? 'Pilih status lain atau hapus filter.' : 'Mulai dengan mengunggah sertifikat atau bukti kegiatan.' }}</p>@if(!request('status'))<a href="{{ route('student.submissions.create') }}" class="button button-secondary">Unggah sertifikat</a>@endif</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Kegiatan</th><th>Kategori</th><th>Dikirim</th><th>Estimasi</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($submissions as $submission)<tr><td><strong>{{ $submission->activity_name }}</strong><small>{{ $submission->organizer }}</small></td><td>{{ $submission->rule->subcategory }}</td><td>{{ $submission->submitted_at?->translatedFormat('d M Y') ?: 'Belum dikirim' }}</td><td>{{ $submission->estimated_points }} poin</td><td><span class="status status-{{ $submission->status }}">{{ match($submission->status) {'draft'=>'Draf','pending'=>'Menunggu','revision'=>'Perlu revisi','approved'=>'Disetujui','rejected'=>'Ditolak'} }}</span></td><td><a class="table-action" href="{{ route('student.submissions.show', $submission) }}" aria-label="Lihat detail">@include('partials.icon', ['name' => 'chevron'])</a></td></tr>@endforeach
            </tbody></table></div>
            <div class="pagination">{{ $submissions->links() }}</div>
        @endif
    </section>
@endsection
