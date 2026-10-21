@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
<div class="page-heading">
    <div><p class="section-kicker">Akun dan otorisasi</p><h1>Manajemen user</h1><p>Temukan akun, periksa role dan unit kerjanya, lalu buka detail untuk mengubah akses.</p></div>
</div>

<section class="data-overview" aria-label="Ringkasan akun">
    <div><span>Seluruh akun</span><strong>{{ number_format($userSummary['total']) }}</strong></div>
    <div><span>Akun aktif</span><strong>{{ number_format($userSummary['active']) }}</strong></div>
    <div><span>Mahasiswa</span><strong>{{ number_format($userSummary['students']) }}</strong></div>
    <div><span>Pengelola</span><strong>{{ number_format($userSummary['staff']) }}</strong></div>
</section>

<form method="GET" class="filter-panel" aria-label="Filter user">
    <div class="filter-control filter-control-search"><span>Cari user</span><label class="search-field">@include('partials.icon', ['name'=>'search'])<input type="search" name="q" value="{{ request('q') }}" placeholder="Nama, email, atau NIM" aria-label="Cari user"></label></div>
    <div class="filter-control"><span>Role</span><label class="select-filter">@include('partials.icon', ['name'=>'filter'])<select name="role" aria-label="Filter role"><option value="">Semua role</option>@foreach($roles as $value=>$label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select></label></div>
    <div class="filter-control"><span>Status akun</span><label class="select-filter"><select name="status" aria-label="Filter status"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label></div>
    <div class="filter-control"><span>Program studi</span><label class="select-filter"><select name="study_program_id" aria-label="Filter program studi"><option value="">Semua prodi</option>@foreach($studyPrograms as $program)<option value="{{ $program->id }}" @selected((string)request('study_program_id') === (string)$program->id)>{{ $program->code }} · {{ $program->name }}</option>@endforeach</select></label></div>
    <div class="filter-actions"><button class="button button-secondary" type="submit">Terapkan filter</button>@if(request()->hasAny(['q','role','status','study_program_id','faculty_id']))<a href="{{ route('admin.users.index') }}" class="button button-quiet">Hapus filter</a>@endif</div>
</form>

<section class="panel panel-table">
    <div class="panel-head data-panel-head"><div><h2>Daftar akun</h2><p>{{ number_format($users->total()) }} akun ditemukan</p></div><span class="result-note">Diurutkan berdasarkan nama</span></div>
    @if($users->isEmpty())
        <div class="empty-state"><h3>User tidak ditemukan</h3><p>Tidak ada akun yang cocok. Ubah kata pencarian atau filter yang digunakan.</p></div>
    @else
        <div class="table-wrap"><table class="admin-data-table user-table">
            <thead><tr><th>Pengguna</th><th>Role</th><th>Unit</th><th>Status akses</th><th>Terdaftar</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>@foreach($users as $account)<tr>
                <td data-label="Pengguna"><strong>{{ $account->name }}</strong><small>{{ $account->profile?->nim ? $account->profile->nim.' · ' : '' }}{{ $account->email }}</small></td>
                <td data-label="Role"><span class="role-badge role-{{ str_replace('_', '-', $account->role) }}">{{ $account->roleLabel() }}</span></td>
                <td data-label="Unit"><strong class="cell-value">{{ $account->studyProgram?->name ?? $account->faculty?->name ?? 'Semua unit' }}</strong>@if($account->studyProgram?->code)<small>{{ $account->studyProgram->code }}</small>@endif</td>
                <td data-label="Status akses"><span class="status {{ $account->is_active ? 'status-approved' : 'status-rejected' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span><small>{{ $account->email_verified_at ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</small></td>
                <td data-label="Terdaftar"><time datetime="{{ $account->created_at->toDateString() }}">{{ $account->created_at->translatedFormat('d M Y') }}</time></td>
                <td><a href="{{ route('admin.users.show', $account) }}" class="table-action table-action-labeled" aria-label="Buka detail user {{ $account->name }}"><span class="table-action-text">Detail</span>@include('partials.icon', ['name'=>'chevron'])</a></td>
            </tr>@endforeach</tbody>
        </table></div>
        <div class="pagination">{{ $users->links() }}</div>
    @endif
</section>
@endsection
