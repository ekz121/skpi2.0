@extends('layouts.app')
@section('title', 'Data Mahasiswa')
@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="section-kicker">Pengelolaan akun</p>
            <h1>Data mahasiswa</h1>
            <p>Cari, periksa, ubah, atau hapus akun mahasiswa yang sudah terdaftar.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="button button-primary">Tambah mahasiswa</a>
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
        @if($students->isEmpty())
            <div class="empty-state"><h3>Mahasiswa tidak ditemukan</h3><p>Coba kata pencarian lain atau tambahkan akun baru.</p></div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mahasiswa</th><th>Program studi</th><th>Angkatan</th><th>Sertifikat</th><th>Total poin</th><th>Kesiapan</th><th></th></tr></thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td><strong>{{ $student->name }}</strong><small>{{ $student->profile?->nim }} · {{ $student->email }}</small></td>
                            <td>{{ $student->profile?->studyProgram?->name ?: 'Belum diatur' }}</td>
                            <td>{{ $student->profile?->cohort ?: '-' }}</td>
                            <td><strong>{{ $student->approved_submissions_count }}</strong></td>
                            <td>{{ (int) $student->approved_points }} poin</td>
                            <td><span class="status {{ $student->approved_submissions_count > 0 ? 'status-approved' : 'status-pending' }}">{{ $student->approved_submissions_count > 0 ? 'Bisa mengajukan' : 'Belum unggah' }}</span></td>
                            <td><a href="{{ route('admin.students.show', $student) }}" class="table-action" aria-label="Buka data {{ $student->name }}">@include('partials.icon', ['name' => 'chevron'])</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $students->links() }}</div>
        @endif
    </section>
@endsection
