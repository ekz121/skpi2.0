@extends('layouts.app')
@section('title', $submission->exists ? 'Perbaiki Pengajuan' : 'Unggah Sertifikat')
@section('content')
    <div class="page-heading"><p class="section-kicker">{{ $submission->exists ? 'Tindak lanjut verifikasi' : 'Pengajuan kegiatan baru' }}</p><h1>{{ $submission->exists ? 'Perbaiki pengajuan' : 'Unggah sertifikat' }}</h1><p>Empat tahap membantu memastikan kegiatan dan bukti sesuai dengan pedoman poin.</p></div>
    @if($submission->status === 'revision' && $submission->admin_note)<div class="review-note"><strong>Catatan admin</strong><p>{{ $submission->admin_note }}</p></div>@endif
    <ol class="stepper" data-stepper aria-label="Tahap pengajuan">
        <li class="active"><span>1</span><p>Pilih kategori<small>Bidang kegiatan</small></p></li>
        <li><span>2</span><p>Jenis kegiatan<small>Aturan poin</small></p></li>
        <li><span>3</span><p>Detail dan bukti<small>Informasi kegiatan</small></p></li>
        <li><span>4</span><p>Ringkasan<small>Periksa kembali</small></p></li>
    </ol>
    <form method="POST" action="{{ $submission->exists ? route('student.submissions.update', $submission) : route('student.submissions.store') }}" enctype="multipart/form-data" class="wizard panel" data-upload-wizard>
        @csrf @if($submission->exists) @method('PUT') @endif
        @php($selectedRule = old('activity_rule_id', $submission->activity_rule_id))
        @php($selectedRuleModel = $rules->firstWhere('id', (int) $selectedRule))
        <section class="wizard-step active" data-step="1">
            <div class="wizard-heading"><span>01</span><div><h2>Pilih kategori kegiatan</h2><p>Pilih bidang yang paling sesuai dengan bukti Anda.</p></div></div>
            <div class="category-options">
                @foreach($rules->pluck('category')->unique()->values() as $index => $category)
                    <label class="category-option"><input type="radio" name="category_picker" value="{{ $category }}" @checked(($selectedRuleModel?->category ?? old('category_picker')) === $category)><span class="category-number">0{{ $index + 1 }}</span><span><strong>{{ $category }}</strong><small>{{ $rules->where('category', $category)->count() }} pilihan kegiatan</small></span>@include('partials.icon', ['name' => 'chevron'])</label>
                @endforeach
            </div>
        </section>
        <section class="wizard-step" data-step="2">
            <div class="wizard-heading"><span>02</span><div><h2>Tentukan jenis kegiatan</h2><p>Estimasi poin ditetapkan otomatis dari pedoman.</p></div></div>
            <label class="field"><span>Jenis, tingkat, atau capaian</span><select name="activity_rule_id" required data-rule-select><option value="">Pilih jenis kegiatan</option>@foreach($rules as $rule)<option value="{{ $rule->id }}" data-category="{{ $rule->category }}" data-points="{{ $rule->points }}" data-evidence="{{ $rule->evidence_label }}" @selected((int)$selectedRule === $rule->id)>{{ $rule->label() }} · {{ $rule->points }} poin</option>@endforeach</select></label>
            <div class="estimate-box" data-estimate><div><small>Estimasi poin</small><strong>{{ $selectedRuleModel?->points ?? 0 }}</strong></div><p data-evidence-label>{{ $selectedRuleModel?->evidence_label ?? 'Pilih kegiatan untuk melihat bukti yang diperlukan.' }}</p></div>
        </section>
        <section class="wizard-step" data-step="3">
            <div class="wizard-heading"><span>03</span><div><h2>Lengkapi detail dan bukti</h2><p>Unggah PDF, JPG, JPEG, atau PNG dengan ukuran maksimal 5 MB.</p></div></div>
            <div class="form-grid two"><label class="field"><span>Nama kegiatan</span><input type="text" name="activity_name" value="{{ old('activity_name', $submission->activity_name) }}" required maxlength="180" data-summary="activity"></label><label class="field"><span>Penyelenggara</span><input type="text" name="organizer" value="{{ old('organizer', $submission->organizer) }}" required maxlength="180" data-summary="organizer"></label></div>
            <div class="form-grid two"><label class="field"><span>Tanggal mulai</span><input type="date" name="started_at" value="{{ old('started_at', $submission->started_at?->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label><label class="field"><span>Tanggal selesai <em>opsional</em></span><input type="date" name="ended_at" value="{{ old('ended_at', $submission->ended_at?->format('Y-m-d')) }}"></label></div>
            <div class="form-grid two"><label class="field"><span>Nomor sertifikat <em>opsional</em></span><input type="text" name="certificate_number" value="{{ old('certificate_number', $submission->certificate_number) }}" maxlength="100"></label><label class="field"><span>Tautan verifikasi <em>opsional</em></span><input type="url" name="verification_url" value="{{ old('verification_url', $submission->verification_url) }}" placeholder="https://"></label></div>
            <label class="upload-zone"><input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png" {{ !$submission->evidence_path ? '' : '' }} data-file-input><span class="upload-icon">@include('partials.icon', ['name' => 'upload'])</span><span><strong>{{ $submission->evidence_original_name ?: 'Pilih dokumen bukti' }}</strong><small data-file-name>PDF, JPG, JPEG, atau PNG, maksimal 5 MB</small></span></label>
        </section>
        <section class="wizard-step" data-step="4">
            <div class="wizard-heading"><span>04</span><div><h2>Periksa ringkasan</h2><p>Pastikan data sesuai dengan dokumen sebelum dikirim.</p></div></div>
            <div class="summary-card"><dl><div><dt>Kategori</dt><dd data-summary-output="category">Belum dipilih</dd></div><div><dt>Jenis kegiatan</dt><dd data-summary-output="rule">Belum dipilih</dd></div><div><dt>Nama kegiatan</dt><dd data-summary-output="activity">Belum diisi</dd></div><div><dt>Penyelenggara</dt><dd data-summary-output="organizer">Belum diisi</dd></div><div><dt>Estimasi poin</dt><dd><strong data-summary-output="points">0 poin</strong></dd></div></dl></div>
            <div class="submission-note">Setelah dikirim, sertifikat langsung tersimpan di akun dan dapat digunakan untuk mengajukan SKPI.</div>
        </section>
        <input type="hidden" name="action" value="submit">
        <div class="wizard-actions"><button class="button button-quiet" type="button" data-step-back hidden>Kembali</button><button class="button button-primary wizard-primary-action" type="button" data-step-next>Lanjutkan</button><button class="button button-primary wizard-primary-action" type="submit" data-step-submit hidden>Simpan sertifikat</button></div>
    </form>
@endsection
