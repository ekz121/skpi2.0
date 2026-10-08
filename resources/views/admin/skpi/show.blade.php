@extends('layouts.app')
@section('title', 'Periksa SKPI')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Pratinjau sumber data</p><h1>{{ $skpi->user->name }}</h1><p>{{ $skpi->user->profile->nim }} · {{ $skpi->user->profile->studyProgram->name }}</p></div><span class="status status-{{ $skpi->status === 'issued' ? 'approved' : ($skpi->status === 'rejected' ? 'rejected' : 'pending') }} large">{{ match($skpi->status) {'pending'=>'Menunggu keputusan','rejected'=>'Ditolak','issued'=>'Telah terbit','failed'=>'Gagal dibuat', default=>'Menunggu keputusan'} }}</span></div>
    <div class="skpi-admin-layout">
        <section class="document-preview">
            <div class="document-head"><img src="{{ asset('assets/polteksi-logo-official.png') }}" alt=""><div><strong>POLITEKNIK SEMEN INDONESIA</strong><span>SURAT KETERANGAN PENDAMPING IJAZAH</span></div></div>
            <h2>Informasi identitas pemilik SKPI</h2><dl class="preview-grid"><div><dt>Nama lengkap</dt><dd>{{ $skpi->user->name }}</dd></div><div><dt>NIM</dt><dd>{{ $skpi->user->profile->nim }}</dd></div><div><dt>Tempat, tanggal lahir</dt><dd>{{ $skpi->user->profile->birthplace }}, {{ $skpi->user->profile->birthdate?->translatedFormat('d F Y') }}</dd></div><div><dt>Tahun masuk</dt><dd>{{ $skpi->user->profile->cohort }}</dd></div><div><dt>Program studi</dt><dd>{{ $skpi->user->profile->studyProgram->name }}</dd></div><div><dt>Nomor ijazah</dt><dd>{{ $skpi->user->profile->diploma_number ?: '[DATA RESMI BELUM TERSEDIA]' }}</dd></div></dl>
            <h2>Aktivitas, prestasi, dan sertifikasi</h2><table class="preview-table"><thead><tr><th>No</th><th>Kegiatan</th><th>Penyelenggara</th><th>Tahun</th><th>Bobot</th></tr></thead><tbody>@foreach($activities as $activity)<tr><td>{{ $loop->iteration }}</td><td>{{ $activity->activity_name }}</td><td>{{ $activity->organizer }}</td><td>{{ $activity->started_at->year }}</td><td>{{ $activity->approved_points }}</td></tr>@endforeach</tbody></table>
            <p class="preview-total">Total bobot terverifikasi: <strong>{{ $points }} poin</strong></p>
        </section>
        <aside class="panel issue-panel"><div class="panel-head"><div><h2>Pemeriksaan akhir</h2><p>Dokumen dibuat otomatis dari profil, capaian pembelajaran, dan sertifikat mahasiswa.</p></div></div><ul class="check-lines"><li class="{{ $activities->isNotEmpty() ? 'done' : '' }}">@include('partials.icon', ['name'=>$activities->isNotEmpty() ? 'check' : 'alert']) Minimal satu sertifikat tersimpan</li><li class="{{ $skpi->user->profile->isComplete() ? 'done' : '' }}">@include('partials.icon', ['name'=>$skpi->user->profile->isComplete() ? 'check' : 'alert']) Data dokumen lengkap</li><li class="{{ filled($skpi->user->profile->studyProgram->learning_outcomes) ? 'done' : '' }}">@include('partials.icon', ['name'=>filled($skpi->user->profile->studyProgram->learning_outcomes) ? 'check' : 'alert']) Capaian pembelajaran prodi tersedia</li></ul>
            @if(in_array($skpi->status, ['pending','rejected','failed']))
                <form method="POST" action="{{ route('admin.skpi.update', $skpi) }}" class="decision-buttons">@csrf @method('PUT')
                    <button class="button button-primary button-block" type="submit" name="decision" value="issue">Setujui dan terbitkan</button>
                    <button class="button button-danger button-block" type="submit" name="decision" value="reject">Tolak permintaan</button>
                </form>
            @else
                <div class="issued-card small"><span>@include('partials.icon', ['name'=>'check'])</span><div><small>Nomor dokumen</small><strong>{{ $skpi->document_number }}</strong><p>{{ $skpi->issued_at->translatedFormat('d F Y, H:i') }}</p></div></div>
                <div class="document-downloads"><a class="button button-primary" href="{{ route('admin.skpi.download', [$skpi, 'pdf']) }}">@include('partials.icon', ['name'=>'download']) PDF</a><a class="button button-secondary" href="{{ route('admin.skpi.download', [$skpi, 'docx']) }}">@include('partials.icon', ['name'=>'download']) Word</a></div>
            @endif
        </aside>
    </div>
@endsection
