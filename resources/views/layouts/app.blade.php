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
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="page-loader" data-page-loader aria-hidden="true"><span></span></div>
    <div class="app-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>
        <aside class="sidebar" data-sidebar>
            <div class="sidebar-head">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}" class="brand-lockup">
                    <span class="brand-logo-card"><img src="{{ asset('assets/polteksi-logo-official.png') }}" alt="Logo Politeknik Semen Indonesia"></span>
                    <span><strong>POLTEKSI</strong><small>SKEM &amp; SKPI</small></span>
                </a>
                <button class="icon-button sidebar-close" type="button" data-sidebar-close aria-label="Tutup menu">@include('partials.icon', ['name' => 'close'])</button>
            </div>

            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <span class="nav-label">Ruang kerja</span>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">@include('partials.icon', ['name' => 'dashboard'])<span>Dashboard</span></a>
                    <a href="{{ route('admin.submissions.index', ['review' => 'new']) }}" class="nav-item {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'certificate'])<span>Sertifikat Masuk</span></a>
                    <a href="{{ route('admin.students') }}" class="nav-item {{ request()->routeIs('admin.students') ? 'active' : '' }}">@include('partials.icon', ['name' => 'users'])<span>Rekap Mahasiswa</span></a>
                    <a href="{{ route('admin.skpi.index') }}" class="nav-item {{ request()->routeIs('admin.skpi.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'document'])<span>Permintaan SKPI</span></a>
                @else
                    <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">@include('partials.icon', ['name' => 'dashboard'])<span>Dashboard</span></a>
                    <a href="{{ route('student.submissions.create') }}" class="nav-item {{ request()->routeIs('student.submissions.create') || request()->routeIs('student.submissions.edit') ? 'active' : '' }}">@include('partials.icon', ['name' => 'upload'])<span>Unggah Sertifikat</span></a>
                    <a href="{{ route('student.submissions.index') }}" class="nav-item {{ request()->routeIs('student.submissions.index') || request()->routeIs('student.submissions.show') ? 'active' : '' }}">@include('partials.icon', ['name' => 'history'])<span>Riwayat Pengajuan</span></a>
                    <a href="{{ route('student.recap') }}" class="nav-item {{ request()->routeIs('student.recap') ? 'active' : '' }}">@include('partials.icon', ['name' => 'chart'])<span>Rekap Poin</span></a>
                    <a href="{{ route('student.guide') }}" class="nav-item {{ request()->routeIs('student.guide') ? 'active' : '' }}">@include('partials.icon', ['name' => 'book'])<span>Panduan</span></a>
                    <a href="{{ route('student.skpi') }}" class="nav-item {{ request()->routeIs('student.skpi*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'document'])<span>Pengajuan SKPI</span></a>
                @endif
            </nav>

            <div class="sidebar-foot">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.profile') }}" class="account-card">
                    <span class="avatar">{{ collect(explode(' ', auth()->user()->name))->map(fn($word) => mb_substr($word, 0, 1))->take(2)->join('') }}</span>
                    <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isAdmin() ? 'Administrator' : auth()->user()->profile?->nim }}</small></span>
                    @include('partials.icon', ['name' => 'chevron'])
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="nav-item logout-button" type="submit">@include('partials.icon', ['name' => 'logout'])<span>Keluar</span></button>
                </form>
            </div>
        </aside>

        <section class="workspace">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-button" type="button" data-sidebar-open aria-label="Buka menu">@include('partials.icon', ['name' => 'menu'])<span>Menu</span></button>
                    <div><span class="topbar-date">{{ now()->translatedFormat('l, d F Y') }}</span><strong class="topbar-context">@yield('context', auth()->user()->isAdmin() ? 'Administrasi SKPI' : 'Portal Mahasiswa')</strong></div>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button" type="button" data-theme-toggle aria-label="Ubah tema">@include('partials.icon', ['name' => 'moon'])</button>
                    @php($notifications = \App\Models\AppNotification::where('user_id', auth()->id())->latest()->limit(8)->get())
                    @php($unreadCount = \App\Models\AppNotification::where('user_id', auth()->id())->whereNull('read_at')->count())
                    <details class="notification-menu">
                        <summary class="icon-button" aria-label="Notifikasi">@include('partials.icon', ['name' => 'bell']) @if($unreadCount)<span class="notification-dot">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif</summary>
                        <div class="notification-popover">
                            <div class="notification-head">
                                <strong>Notifikasi</strong>
                                @if($notifications->isNotEmpty())
                                    <form method="POST" action="{{ route('notifications.destroy-all') }}">@csrf @method('DELETE')<button type="submit">Hapus semua</button></form>
                                @endif
                            </div>
                            @forelse($notifications as $notice)
                                <div class="notification-item">
                                    <a href="{{ $notice->url ?: '#' }}"><span>{{ $notice->title }}</span><small>{{ $notice->message }}</small><time>{{ $notice->created_at->diffForHumans() }}</time></a>
                                    <form method="POST" action="{{ route('notifications.destroy', $notice) }}">@csrf @method('DELETE')<button class="notification-delete" type="submit" aria-label="Hapus notifikasi {{ $notice->title }}" title="Hapus notifikasi">@include('partials.icon', ['name' => 'trash'])</button></form>
                                </div>
                            @empty
                                <p class="empty-compact">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </details>
                </div>
            </header>

            <main id="main-content" class="content">
                @if(str_contains(auth()->user()->email, '@demo.'))
                    <div class="demo-banner"><strong>Data simulasi</strong><span>Seluruh identitas dan angka pada akun ini hanya untuk demonstrasi antarmuka.</span></div>
                @endif
                @if(session('success'))<div class="alert alert-success" role="status">@include('partials.icon', ['name' => 'check'])<span>{{ session('success') }}</span></div>@endif
                @if($errors->any())
                    <div class="alert alert-error" role="alert">@include('partials.icon', ['name' => 'alert'])<div><strong>Permintaan belum dapat diproses.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
                @endif
                @yield('content')
            </main>
        </section>
    </div>
    <script src="{{ asset('assets/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
