@extends('layouts.app')
@section('title', 'Penerbitan SKPI')
@section('content')
    <div class="page-heading"><p class="section-kicker">Keputusan dan dokumen</p><h1>Permintaan SKPI</h1><p>Pilih satu atau banyak permintaan untuk menerbitkan, menolak, atau mengunduh dokumen tanpa membuka setiap baris.</p></div>
    <form method="GET" class="filter-bar"><label class="select-filter">@include('partials.icon', ['name' => 'filter'])<select name="status" onchange="this.form.submit()"><option value="">Semua status</option>@foreach(['pending'=>'Menunggu','issued'=>'Terbit','rejected'=>'Ditolak','failed'=>'Gagal dibuat'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>@if(request('status'))<a href="{{ route('admin.skpi.index') }}" class="text-link">Hapus filter</a>@endif</form>
    <section class="panel panel-table">
        @if($requests->isEmpty())
            <div class="empty-state">@include('partials.icon', ['name' => 'document'])<h3>Belum ada permintaan SKPI</h3><p>Permintaan mahasiswa akan muncul di sini setelah dikirim.</p></div>
        @else
            <form method="POST" action="{{ route('admin.skpi.bulk') }}" data-bulk-form>@csrf
                <div class="bulk-toolbar" data-bulk-toolbar>
                    <span><strong data-selected-count>0</strong> dipilih</span>
                    <div class="bulk-actions">
                        <button class="button button-primary" type="submit" name="action" value="issue">Setujui dan terbitkan</button>
                        <button class="button button-danger" type="submit" name="action" value="reject">Tolak</button>
                        <button class="button button-secondary" type="submit" name="action" value="download">@include('partials.icon', ['name' => 'download']) Unduh ZIP</button>
                    </div>
                </div>
                <div class="table-wrap"><table><thead><tr><th class="select-column"><label class="table-check"><input type="checkbox" data-select-all aria-label="Pilih semua pada halaman ini"></label></th><th>Mahasiswa</th><th>Program studi</th><th>Diajukan</th><th>Nomor dokumen</th><th>Status</th><th>Dokumen</th><th></th></tr></thead><tbody>
                @foreach($requests as $item)
                    <tr><td class="select-column"><label class="table-check"><input type="checkbox" name="request_ids[]" value="{{ $item->id }}" data-select-item aria-label="Pilih SKPI {{ $item->user->name }}"></label></td><td><strong>{{ $item->user->name }}</strong><small>{{ $item->user->profile?->nim }}</small></td><td>{{ $item->user->profile?->studyProgram?->name }}</td><td>{{ $item->created_at->translatedFormat('d M Y') }}</td><td>{{ $item->document_number ?: 'Belum diterbitkan' }}</td><td><span class="status status-{{ $item->status === 'issued' ? 'approved' : ($item->status === 'rejected' ? 'rejected' : 'pending') }}">{{ match($item->status) {'pending'=>'Menunggu','issued'=>'Terbit','rejected'=>'Ditolak','failed'=>'Gagal dibuat', default=>'Menunggu'} }}</span></td><td>@if($item->status === 'issued')<span class="document-links"><a href="{{ route('admin.skpi.download', [$item, 'pdf']) }}">PDF</a><a href="{{ route('admin.skpi.download', [$item, 'docx']) }}">Word</a></span>@else<span class="muted">Belum tersedia</span>@endif</td><td><a href="{{ route('admin.skpi.show', $item) }}" class="table-action" aria-label="Periksa SKPI {{ $item->user->name }}">@include('partials.icon', ['name' => 'chevron'])</a></td></tr>
                @endforeach
                </tbody></table></div>
            </form>
            <div class="pagination">{{ $requests->links() }}</div>
        @endif
    </section>
@endsection
