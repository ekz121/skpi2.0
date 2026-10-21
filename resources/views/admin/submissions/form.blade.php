@extends('layouts.app')
@section('title', $submission->exists ? 'Ubah Sertifikat' : 'Tambah Sertifikat')
@section('context', 'Administrasi Sertifikat')
@section('content')
    <div class="page-heading">
        <p class="section-kicker">{{ $submission->exists ? 'Perbaiki data' : 'Entri admin' }}</p>
        <h1>{{ $submission->exists ? 'Ubah sertifikat' : 'Tambah sertifikat mahasiswa' }}</h1>
        <p>Data yang disimpan langsung aktif pada rekap mahasiswa. Poin mengikuti aturan kegiatan yang dipilih.</p>
    </div>
    @php($selectedRuleId = old('activity_rule_id', $submission->activity_rule_id))
    @php($selectedRuleModel = $rules->firstWhere('id', (int) $selectedRuleId))
    @php($usesCustomType = $selectedRuleModel?->category === \App\Models\Submission::CUSTOM_CATEGORY)
    <form method="POST" enctype="multipart/form-data" action="{{ $submission->exists ? route('admin.submissions.update', $submission) : route('admin.submissions.store') }}" class="panel form-panel form-stack" novalidate data-validate-form>
        @csrf
        @if($submission->exists) @method('PUT') @endif

        @if($submission->exists)
            <input type="hidden" name="user_id" value="{{ $submission->user_id }}">
            <div class="read-only-note"><strong>{{ $submission->user->name }}</strong><span>{{ $submission->user->profile?->nim }} · {{ $submission->user->profile?->studyProgram?->name }}</span></div>
        @else
            <label class="field"><span>Mahasiswa <b class="required-mark" aria-hidden="true">*</b></span><select name="user_id" required><option value="">Pilih mahasiswa</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(old('user_id') == $student->id)>{{ $student->name }} · {{ $student->profile?->nim }} · {{ $student->profile?->studyProgram?->code }}</option>@endforeach</select><x-field-error name="user_id" client /></label>
        @endif

        <label class="field"><span>Jenis kegiatan <b class="required-mark" aria-hidden="true">*</b></span><select name="activity_rule_id" required data-custom-rule-select><option value="">Pilih aturan kegiatan</option>@foreach($rules as $rule)<option value="{{ $rule->id }}" data-entry-mode="{{ $rule->category === \App\Models\Submission::CUSTOM_CATEGORY ? 'custom' : 'standard' }}" @selected((int) $selectedRuleId === $rule->id)>{{ $rule->category }} · {{ $rule->label() }}@if($rule->category !== \App\Models\Submission::CUSTOM_CATEGORY) · {{ $rule->points }} poin @endif</option>@endforeach</select><x-field-error name="activity_rule_id" client /></label>
        <label class="field" data-admin-custom-rule-field @if(!$usesCustomType) hidden @endif><span>Jenis kegiatan lainnya <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="custom_activity_type" value="{{ old('custom_activity_type', $submission->custom_activity_type) }}" maxlength="180" @required($usesCustomType) @disabled(!$usesCustomType) placeholder="Contoh: Sertifikat kepanitiaan"><x-field-error name="custom_activity_type" client /></label>
        <div class="form-grid two-columns">
            <label class="field"><span>Nama kegiatan <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="activity_name" value="{{ old('activity_name', $submission->activity_name) }}" maxlength="180" required><x-field-error name="activity_name" client /></label>
            <label class="field"><span>Penyelenggara <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="organizer" value="{{ old('organizer', $submission->organizer) }}" maxlength="180" required><x-field-error name="organizer" client /></label>
            <label class="field"><span>Tanggal mulai <b class="required-mark" aria-hidden="true">*</b></span><input type="date" name="started_at" value="{{ old('started_at', $submission->started_at?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required><x-field-error name="started_at" client /></label>
            <label class="field"><span>Tanggal selesai <em>opsional</em></span><input type="date" name="ended_at" value="{{ old('ended_at', $submission->ended_at?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"><x-field-error name="ended_at" client /></label>
            <label class="field"><span>Nomor sertifikat <em>opsional</em></span><input type="text" name="certificate_number" value="{{ old('certificate_number', $submission->certificate_number) }}" maxlength="100"><x-field-error name="certificate_number" client /></label>
            <label class="field"><span>Tautan verifikasi <em>opsional</em></span><input type="url" name="verification_url" value="{{ old('verification_url', $submission->verification_url) }}" maxlength="500" pattern="https?://.*" placeholder="https://"><x-field-error name="verification_url" client /></label>
        </div>
        <label class="field"><span>{{ $submission->evidence_path ? 'Ganti berkas bukti' : 'Berkas bukti' }} @if($submission->evidence_path)<em>opsional</em>@else<b class="required-mark" aria-hidden="true">*</b>@endif</span><input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" @required(!$submission->evidence_path)><small>PDF, JPG, JPEG, atau PNG. Maksimal 5 MB.</small><x-field-error name="evidence" client /></label>
        @if($submission->evidence_path)<a href="{{ route('evidence.download', $submission) }}" class="text-link">Unduh berkas saat ini: {{ $submission->evidence_original_name }}</a>@endif
        <div class="form-actions"><a href="{{ $submission->exists ? route('admin.submissions.show', $submission) : route('admin.submissions.index') }}" class="button button-quiet">Batal</a><button class="button button-primary" type="submit">{{ $submission->exists ? 'Simpan perubahan' : 'Simpan sertifikat' }}</button></div>
    </form>
@endsection
