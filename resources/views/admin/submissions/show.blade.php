@extends('layouts.app')
@section('title', 'Periksa Pengajuan')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Pemeriksaan bukti</p><h1>{{ $submission->activity_name }}</h1><p>{{ $submission->user->name }} · {{ $submission->user->profile?->nim }}</p></div><span class="status status-{{ $submission->status }} large">{{ match($submission->status) {'pending'=>'Menunggu verifikasi','revision'=>'Perlu revisi','approved'=>'Disetujui','rejected'=>'Ditolak'} }}</span></div>
    <div class="verification-layout">
        <section class="panel"><div class="panel-head"><div><h2>Data pengajuan</h2><p>Bandingkan isian dengan bukti yang diunggah.</p></div></div><dl class="detail-list"><div><dt>Mahasiswa</dt><dd>{{ $submission->user->name }}<small>{{ $submission->user->profile?->studyProgram?->name }}</small></dd></div><div><dt>Kategori</dt><dd>{{ $submission->rule->category }}</dd></div><div><dt>Aturan kegiatan</dt><dd>{{ $submission->rule->label() }}</dd></div><div><dt>Penyelenggara</dt><dd>{{ $submission->organizer }}</dd></div><div><dt>Tanggal</dt><dd>{{ $submission->started_at->translatedFormat('d F Y') }}</dd></div><div><dt>Nomor sertifikat</dt><dd>{{ $submission->certificate_number ?: 'Tidak dicantumkan' }}</dd></div><div><dt>Bukti yang disyaratkan</dt><dd>{{ $submission->rule->evidence_label }}</dd></div></dl>
            @if($submission->evidence_path)<a href="{{ route('evidence.download', $submission) }}" class="evidence-file">@include('partials.icon', ['name' => 'file'])<span><strong>{{ $submission->evidence_original_name }}</strong><small>Unduh untuk memeriksa dokumen</small></span>@include('partials.icon', ['name' => 'download'])</a>@else<div class="evidence-file unavailable">@include('partials.icon', ['name' => 'file'])<span><strong>Berkas tidak tersedia</strong><small>Entri ini adalah data simulasi.</small></span></div>@endif
        </section>
        <aside class="panel decision-panel"><div class="rule-score"><small>Poin sesuai pedoman</small><strong>{{ $submission->rule->points }}</strong><span>poin</span></div><div class="decision-rule"><strong>Dasar keputusan</strong><p>{{ $submission->rule->subcategory }}, {{ $submission->rule->label() }}.</p></div>
            @if(in_array($submission->status, ['pending','approved']))
            <form method="POST" action="{{ route('admin.submissions.decide', $submission) }}" class="form-stack" data-decision-form>@csrf @method('PUT')
                <label class="field"><span>Keputusan</span><select name="decision" required data-decision-select><option value="">Pilih keputusan</option><option value="approved">Setujui pengajuan</option><option value="revision">Minta revisi</option><option value="rejected">Tolak pengajuan</option></select></label>
                <label class="field"><span>Catatan admin</span><textarea name="note" rows="4" maxlength="1000" data-decision-note placeholder="Wajib untuk revisi atau penolakan"></textarea></label>
                <button class="button button-primary button-block" type="submit">Simpan keputusan</button>
            </form>
            @else<div class="status-message"><strong>Keputusan sudah diberikan</strong><p>{{ $submission->admin_note ?: 'Pengajuan telah diproses.' }}</p></div>@endif
        </aside>
    </div>
@endsection
