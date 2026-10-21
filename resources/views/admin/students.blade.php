@extends('layouts.app')
@section('title', 'Data Mahasiswa')
@section('content')
    <div class="page-heading">
        <div>
            <p class="section-kicker">Daftar akademik dan akun</p>
            <h1>Data mahasiswa</h1>
            <p>Daftar akademik memuat seluruh mahasiswa sumber. Status akun menunjukkan siapa yang sudah mendaftar ke sistem.</p>
        </div>
    </div>

    <div class="metrics-row admin-metrics student-directory-metrics">
        <article class="metric-card"><div><small>Daftar akademik</small><strong>{{ $registryCount }}</strong><span>mahasiswa sumber</span></div></article>
        <article class="metric-card"><div><small>Sudah mendaftar</small><strong>{{ $registeredCount }}</strong><span>tertambat ke akun</span></div></article>
        <article class="metric-card"><div><small>Belum mendaftar</small><strong>{{ $unregisteredCount }}</strong><span>masih dapat membuat akun</span></div></article>
        <article class="metric-card"><div><small>Seluruh akun mahasiswa</small><strong>{{ $accountCount }}</strong><span>termasuk akun lama</span></div></article>
    </div>

    <form method="GET" class="filter-bar admin-filter">
        <label class="search-field">
            @include('partials.icon', ['name' => 'search'])
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIM, email, atau program studi" aria-label="Cari mahasiswa">
        </label>
        <button class="button button-secondary" type="submit">Cari</button>
        @if(request('q'))<a href="{{ route('admin.students') }}" class="text-link">Reset</a>@endif
    </form>

    <section class="panel panel-table">
        <div class="panel-head">
            <div><h2>Daftar akademik</h2><p>{{ $students->total() }} data ditemukan</p></div>
        </div>
        @if($students->isEmpty())
            <div class="empty-state"><h3>Mahasiswa tidak ditemukan</h3><p>Coba kata pencarian lain.</p></div>
        @else
            <div class="table-wrap">
                <table class="directory-table">
                    <thead><tr><th>Mahasiswa</th><th>Program studi</th><th>Angkatan</th><th>Akun</th><th>Verifikasi</th><th>Sertifikat</th><th></th></tr></thead>
                    <tbody>
                    @foreach($students as $registry)
                        @php($account = $registry->user)
                        <tr>
                            <td data-label="Mahasiswa"><strong>{{ $registry->name }}</strong><small>{{ $registry->nim }}</small></td>
                            <td data-label="Program studi">{{ $registry->studyProgram?->name ?: 'Belum diatur' }}</td>
                            <td data-label="Angkatan">{{ $registry->cohort }}</td>
                            <td data-label="Akun">
                                @if($account)
                                    <strong>{{ $account->email }}</strong>
                                    <small>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</small>
                                @else
                                    <span class="status status-draft">Belum mendaftar</span>
                                @endif
                            </td>
                            <td data-label="Verifikasi">
                                @if($account?->hasVerifiedEmail())
                                    <span class="status status-approved">Terverifikasi</span>
                                @elseif($account)
                                    <span class="status status-pending">Belum verifikasi</span>
                                @else
                                    <span aria-hidden="true">-</span><span class="sr-only">Belum memiliki akun</span>
                                @endif
                            </td>
                            <td data-label="Sertifikat">{{ $account ? (int) $account->approved_submissions_count : 0 }}</td>
                            <td>
                                @if($account)
                                    <a href="{{ route('admin.students.show', $account) }}" class="table-action" aria-label="Buka akun {{ $registry->name }}">@include('partials.icon', ['name' => 'chevron'])</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $students->links() }}</div>
        @endif
    </section>

    @if($orphanStudents->isNotEmpty())
        <section class="panel panel-table dashboard-secondary">
            <div class="panel-head">
                <div><h2>Akun belum tertaut ke daftar akademik</h2><p>{{ $orphanStudents->count() }} akun tetap ditampilkan agar tidak ada data pendaftar yang tersembunyi.</p></div>
            </div>
            <div class="table-wrap">
                <table class="directory-table">
                    <thead><tr><th>Mahasiswa</th><th>NIM</th><th>Program studi</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @foreach($orphanStudents as $account)
                        <tr>
                            <td data-label="Mahasiswa"><strong>{{ $account->name }}</strong><small>{{ $account->email }}</small></td>
                            <td data-label="NIM">{{ $account->profile?->nim ?: '-' }}</td>
                            <td data-label="Program studi">{{ $account->profile?->studyProgram?->name ?: 'Belum diatur' }}</td>
                            <td data-label="Status"><span class="status {{ $account->is_active ? 'status-approved' : 'status-rejected' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><a href="{{ route('admin.students.show', $account) }}" class="table-action" aria-label="Buka akun {{ $account->name }}">@include('partials.icon', ['name' => 'chevron'])</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
