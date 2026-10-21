@extends('layouts.app')
@section('title', 'Periksa SKPI')
@section('content')
    @php
        $statusLabels = ['pending' => 'Menunggu keputusan', 'rejected' => 'Ditolak', 'issued' => 'Telah terbit', 'failed' => 'Gagal dibuat', 'withdrawn' => 'Ditarik'];
        $statusClasses = ['issued' => 'approved', 'withdrawn' => 'withdrawn', 'rejected' => 'rejected', 'failed' => 'failed', 'pending' => 'pending'];
    @endphp
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Pratinjau sumber data</p><h1>{{ $skpi->user->name }}</h1><p>{{ $skpi->user->profile->nim }} · {{ $skpi->user->profile->studyProgram->name }}</p></div><span class="status status-{{ $statusClasses[$skpi->status] ?? 'pending' }} large">{{ $statusLabels[$skpi->status] ?? 'Menunggu keputusan' }}</span></div>
    <div class="skpi-admin-layout">
        <section class="document-preview">
            <div class="document-head"><img src="{{ asset('assets/polteksi-logo-official.png') }}" alt="Logo Politeknik Semen Indonesia"><div><strong>POLITEKNIK SEMEN INDONESIA</strong><span>SURAT KETERANGAN PENDAMPING IJAZAH</span></div></div>
            <h2>Informasi identitas pemilik SKPI</h2><dl class="preview-grid"><div><dt>Nama lengkap</dt><dd>{{ $skpi->user->name }}</dd></div><div><dt>NIM</dt><dd>{{ $skpi->user->profile->nim }}</dd></div><div><dt>Tempat, tanggal lahir</dt><dd>{{ $skpi->user->profile->birthplace }}, {{ $skpi->user->profile->birthdate?->translatedFormat('d F Y') }}</dd></div><div><dt>Tahun masuk</dt><dd>{{ $skpi->user->profile->cohort }}</dd></div><div><dt>Program studi</dt><dd>{{ $skpi->user->profile->studyProgram->name }}</dd></div><div><dt>Nomor ijazah</dt><dd>{{ $skpi->user->profile->diploma_number ?: '[DATA RESMI BELUM TERSEDIA]' }}</dd></div></dl>
            <h2>Aktivitas, prestasi, dan sertifikasi</h2><div class="table-wrap"><table class="preview-table"><thead><tr><th>No</th><th>Kegiatan</th><th>Penyelenggara</th><th>Tahun</th></tr></thead><tbody>@foreach($activities as $activity)<tr><td>{{ $loop->iteration }}</td><td>{{ $activity->activity_name }}<small>{{ $activity->activityTypeLabel() }}</small></td><td>{{ $activity->organizer }}</td><td>{{ $activity->started_at->year }}</td></tr>@endforeach</tbody></table></div>
        </section>
        <aside class="panel issue-panel">
            <div class="panel-head"><div><h2>Pemeriksaan akhir</h2><p>Dokumen dibuat otomatis dari profil, template resmi, dan sertifikat mahasiswa.</p></div></div>
            <ul class="check-lines"><li class="{{ $activities->isNotEmpty() ? 'done' : '' }}">@include('partials.icon', ['name'=>$activities->isNotEmpty() ? 'check' : 'alert']) Minimal satu sertifikat tersimpan</li><li class="{{ $skpi->user->profile->isComplete() ? 'done' : '' }}">@include('partials.icon', ['name'=>$skpi->user->profile->isComplete() ? 'check' : 'alert']) Data dokumen lengkap</li><li class="done">@include('partials.icon', ['name'=>'check']) Template Word resmi tersedia</li></ul>
            @if(in_array($skpi->status, ['pending','rejected','failed']))
                <form method="POST" action="{{ route('admin.skpi.update', $skpi) }}" class="decision-buttons">@csrf @method('PUT')
                    <button class="button button-primary button-block" type="submit" name="decision" value="issue">Setujui dan terbitkan</button>
                    <button class="button button-danger button-block" type="submit" name="decision" value="reject">Tolak permintaan</button>
                </form>
            @else
                <div class="issued-card small {{ $skpi->status === 'withdrawn' ? 'invalid-document' : '' }}"><span>@include('partials.icon', ['name' => $skpi->status === 'withdrawn' ? 'alert' : 'check'])</span><div><small>Nomor dokumen</small><strong>{{ $skpi->document_number }}</strong><p>{{ $skpi->status === 'withdrawn' ? 'Tidak berlaku sejak '.($skpi->withdrawals->sortByDesc('withdrawn_at')->first()?->withdrawn_at?->translatedFormat('d F Y, H:i') ?? '-') : $skpi->issued_at->translatedFormat('d F Y, H:i') }}</p></div></div>
                @if($skpi->status === 'issued')
                    <div class="admin-action-stack"><a class="button button-primary button-block" href="{{ route('admin.skpi.download', $skpi) }}">@include('partials.icon', ['name'=>'download']) Unduh Word</a><button class="button button-danger button-block" type="button" data-dialog-open="withdraw-skpi">Tarik SKPI</button></div>
                @else
                    <button class="button button-secondary button-block" type="button" disabled aria-disabled="true">Dokumen Word tidak berlaku</button>
                @endif
            @endif
            @if($skpi->withdrawals->isNotEmpty())
                <section class="withdrawal-history"><h3>Riwayat penarikan</h3>@foreach($skpi->withdrawals->sortByDesc('withdrawn_at') as $withdrawal)<article><strong>{{ $withdrawal->document_number }}</strong><p>{{ $withdrawal->reason }}</p><small>{{ $withdrawal->admin?->name ?? 'Admin' }} · {{ $withdrawal->withdrawn_at->translatedFormat('d F Y, H:i') }}</small></article>@endforeach</section>
            @endif
        </aside>
    </div>
    @if($skpi->status === 'issued')
        <dialog class="confirm-dialog" id="withdraw-skpi" data-dialog @if($errors->has('withdrawal_reason')) data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.skpi.withdraw', $skpi) }}" class="dialog-card" novalidate data-validate-form>@csrf @method('PUT')
                <div class="dialog-head"><div><p class="section-kicker">Tindakan berbahaya</p><h2>Tarik SKPI ini?</h2></div><button class="icon-button" type="button" data-dialog-close aria-label="Tutup dialog">@include('partials.icon', ['name' => 'close'])</button></div>
                <p>Nomor <strong>{{ $skpi->document_number }}</strong> akan ditandai tidak berlaku. Dokumen tidak dihapus dan tetap tersimpan dalam riwayat audit.</p>
                <label class="field"><span>Alasan penarikan <b class="required-mark" aria-hidden="true">*</b></span><textarea name="withdrawal_reason" rows="4" maxlength="1000" required placeholder="Jelaskan alasan agar mahasiswa memahami tindakan yang diperlukan.">{{ old('withdrawal_reason') }}</textarea><x-field-error name="withdrawal_reason" client /></label>
                <div class="dialog-actions"><button class="button button-quiet" type="button" data-dialog-close>Batal</button><button class="button button-danger" type="submit">Tarik SKPI</button></div>
            </form>
        </dialog>
    @endif
@endsection
