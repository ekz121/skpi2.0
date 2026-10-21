@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
<div class="page-heading">
    <div><p class="section-kicker">Akun dan otorisasi</p><h1>Manajemen user</h1><p>Cari, periksa, ubah, aktifkan, atau nonaktifkan akun yang sudah terdaftar.</p></div>
</div>

<form method="GET" class="filter-bar admin-filter">
    <label class="search-field">@include('partials.icon', ['name'=>'search'])<input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau NIM" aria-label="Cari user"></label>
    <label class="select-filter">@include('partials.icon', ['name'=>'filter'])<select name="role" aria-label="Filter role"><option value="">Semua role</option>@foreach($roles as $value=>$label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select></label>
    <label class="select-filter"><select name="status" aria-label="Filter status"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label>
    <label class="select-filter"><select name="study_program_id" aria-label="Filter program studi"><option value="">Semua prodi</option>@foreach($studyPrograms as $program)<option value="{{ $program->id }}" @selected((string)request('study_program_id') === (string)$program->id)>{{ $program->code }} · {{ $program->name }}</option>@endforeach</select></label>
    <button class="button button-secondary" type="submit">Terapkan</button>
    @if(request()->hasAny(['q','role','status','study_program_id','faculty_id']))<a href="{{ route('admin.users.index') }}" class="text-link">Reset</a>@endif
</form>

<section class="panel panel-table">
    @if($users->isEmpty())
        <div class="empty-state"><h3>User tidak ditemukan</h3><p>Ubah kata pencarian atau filter yang digunakan.</p></div>
    @else
        <div class="table-wrap"><table class="user-table">
            <thead><tr><th>User</th><th>Role</th><th>Unit</th><th>Status</th><th>Terdaftar</th><th></th></tr></thead>
            <tbody>@foreach($users as $account)<tr>
                <td><strong>{{ $account->name }}</strong><small>{{ $account->profile?->nim ? $account->profile->nim.' · ' : '' }}{{ $account->email }}</small></td>
                <td data-label="Role"><span class="role-badge">{{ $account->roleLabel() }}</span></td>
                <td data-label="Unit">{{ $account->studyProgram?->name ?? $account->faculty?->name ?? 'Semua unit' }}<small>{{ $account->studyProgram?->code }}</small></td>
                <td data-label="Status"><span class="status {{ $account->is_active ? 'status-approved' : 'status-rejected' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span><small>{{ $account->email_verified_at ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</small></td>
                <td data-label="Terdaftar">{{ $account->created_at->translatedFormat('d M Y') }}</td>
                <td><a href="{{ route('admin.users.show', $account) }}" class="table-action" aria-label="Buka user {{ $account->name }}">@include('partials.icon', ['name'=>'chevron'])</a></td>
            </tr>@endforeach</tbody>
        </table></div>
        <div class="pagination">{{ $users->links() }}</div>
    @endif
</section>
@endsection
