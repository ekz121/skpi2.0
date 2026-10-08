<!doctype html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · SKEM Polteksi</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <script>document.documentElement.classList.add('js'); document.documentElement.dataset.theme = localStorage.getItem('skem-theme') || 'light';</script>
</head>
<body class="auth-body">
    <a href="#main-content" class="skip-link">Lewati ke formulir</a>
    <main id="main-content" class="auth-shell">
        <section class="auth-brand" aria-label="Tentang sistem">
            <div class="brand-lockup brand-lockup-light auth-brand-lockup">
                <span class="brand-logo-card auth-logo-card"><img src="{{ asset('assets/polteksi-logo-official.png') }}" alt="Logo Politeknik Semen Indonesia"></span>
                <span><strong>PORTAL AKADEMIK</strong><small>SKEM &amp; SKPI POLTEKSI</small></span>
            </div>
            <div class="auth-intro">
                <p class="section-kicker">Politeknik Semen Indonesia</p>
                <h1>Simpan kegiatan, ajukan, terbitkan SKPI.</h1>
                <p>Satu ruang kerja untuk mahasiswa dan pengelola kemahasiswaan, dari bukti kegiatan sampai dokumen pendamping ijazah.</p>
            </div>
            <p class="auth-brand-note">Satu sertifikat tersimpan sudah cukup untuk mengajukan SKPI.</p>
        </section>
        <section class="auth-panel">
            <div class="auth-panel-inner">
                <button class="icon-button auth-theme" type="button" data-theme-toggle aria-label="Ubah tema">
                    @include('partials.icon', ['name' => 'moon'])
                </button>
                @if(session('status'))<div class="alert alert-info">{{ session('status') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-error" role="alert">
                        <strong>Periksa kembali isian Anda.</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </main>
    <script src="{{ asset('assets/app.js') }}" defer></script>
</body>
</html>
