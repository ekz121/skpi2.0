@extends('layouts.app')
@section('title', 'Sertifikat Masuk')
@section('context', 'Administrasi Sertifikat')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Kotak masuk sertifikat</p><h1>Sertifikat mahasiswa</h1><p>Periksa unggahan mahasiswa tanpa menahan poin maupun pengajuan SKPI mereka.</p></div><a href="{{ route('admin.submissions.create') }}" class="button button-secondary">Tambah sertifikat</a></div>

    <section class="data-overview data-overview-three" aria-label="Ringkasan sertifikat">
        <div><span>Seluruh sertifikat</span><strong>{{ number_format($certificateCount) }}</strong></div>
        <div><span>Belum dicek</span><strong>{{ number_format($newCount) }}</strong><small>perlu ditinjau admin</small></div>
        <div><span>Sudah dicek</span><strong>{{ number_format($checkedCount) }}</strong></div>
    </section>

    <form method="GET" class="filter-panel filter-panel-compact" aria-label="Filter sertifikat">
        <div class="filter-control filter-control-search"><span>Cari sertifikat</span><label class="search-field">@include('partials.icon', ['name' => 'search'])<input type="search" name="q" value="{{ request('q') }}" placeholder="Nama, NIM, kegiatan, atau nomor sertifikat" aria-label="Cari sertifikat"></label></div>
        <div class="filter-control"><span>Pemeriksaan</span><label class="select-filter">@include('partials.icon', ['name' => 'filter'])<select name="review" aria-label="Filter pemeriksaan"><option value="">Semua pemeriksaan</option><option value="new" @selected(request('review') === 'new')>Belum dicek</option><option value="checked" @selected(request('review') === 'checked')>Sudah dicek</option></select></label></div>
        <div class="filter-control"><span>Kategori</span><label class="select-filter"><select name="category" aria-label="Filter kategori"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></label></div>
        <div class="filter-actions"><button class="button button-secondary" type="submit">Terapkan filter</button>@if(request()->hasAny(['q','review','category']))<a href="{{ route('admin.submissions.index') }}" class="button button-quiet">Hapus filter</a>@endif</div>
    </form>

    <section class="panel panel-table">
        <div class="panel-head data-panel-head"><div><h2>Daftar sertifikat</h2><p>{{ number_format($submissions->total()) }} sertifikat ditemukan</p></div><span class="result-note">Terbaru ditampilkan lebih dulu</span></div>
        @if($submissions->isEmpty())
            <div class="empty-state">@include('partials.icon', ['name' => 'certificate'])<h3>Sertifikat tidak ditemukan</h3><p>Tidak ada unggahan yang cocok. Ubah filter atau tunggu sertifikat baru dari mahasiswa.</p></div>
        @else
            <div class="table-wrap"><table class="admin-data-table certificate-table"><thead><tr><th>Mahasiswa</th><th>Kegiatan</th><th>Kategori</th><th>Waktu masuk</th><th>Pemeriksaan</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>
                @foreach($submissions as $submission)
                    <tr>
                        <td data-label="Mahasiswa"><strong>{{ $submission->user->name }}</strong><small>{{ $submission->user->profile?->nim }}@if($submission->user->profile?->studyProgram?->code) · {{ $submission->user->profile->studyProgram->code }}@endif</small></td>
                        <td data-label="Kegiatan"><strong>{{ $submission->activity_name }}</strong><small>{{ $submission->organizer }} · {{ $submission->pointsLabel() }}</small></td>
                        <td data-label="Kategori"><strong class="cell-value">{{ $submission->categoryLabel() }}</strong><small>{{ $submission->activityTypeLabel() }}</small></td>
                        <td data-label="Waktu masuk"><time datetime="{{ $submission->submitted_at?->toIso8601String() }}">{{ $submission->submitted_at?->translatedFormat('d M Y') }}</time><small>{{ $submission->submitted_at?->translatedFormat('H:i') }} WIB</small></td>
                        <td data-label="Pemeriksaan">@if($submission->admin_checked_at)<span class="status status-approved">Sudah dicek</span><small>{{ $submission->checker?->name }} · {{ $submission->admin_checked_at->translatedFormat('d M Y') }}</small>@else<span class="status status-pending">Belum dicek</span><small>Menunggu pemeriksaan admin</small>@endif</td>
                        <td><a href="{{ route('admin.submissions.show', $submission) }}" class="table-action table-action-labeled" aria-label="Periksa sertifikat {{ $submission->activity_name }}"><span class="table-action-text">Periksa</span>@include('partials.icon', ['name' => 'chevron'])</a></td>
                    </tr>
                @endforeach
            </tbody></table></div><div class="pagination">{{ $submissions->links() }}</div>
        @endif
    </section>
@endsection
