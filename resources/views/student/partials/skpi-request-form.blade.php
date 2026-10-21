<form method="POST" action="{{ route('student.skpi.request') }}" class="form-stack skpi-request-form" novalidate data-validate-form>
    @csrf
    <div class="form-grid two">
        <label class="field"><span>Tempat lahir <b class="required-mark" aria-hidden="true">*</b></span><input type="text" name="birthplace" value="{{ old('birthplace', auth()->user()->profile->birthplace) }}" maxlength="100" required @class(['is-invalid' => $errors->has('birthplace')])><x-field-error name="birthplace" client /></label>
        <label class="field"><span>Tanggal lahir <b class="required-mark" aria-hidden="true">*</b></span><input type="date" name="birthdate" value="{{ old('birthdate', auth()->user()->profile->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}" required @class(['is-invalid' => $errors->has('birthdate')])><x-field-error name="birthdate" client /></label>
    </div>
    <button class="button button-primary" type="submit" @disabled($approvedSubmissions->isEmpty())>{{ $skpi ? 'Ajukan SKPI baru' : 'Ajukan SKPI' }}</button>
</form>
@if($approvedSubmissions->isEmpty())<p class="field-hint">Unggah sedikitnya satu sertifikat untuk mengaktifkan tombol pengajuan.</p>@endif
