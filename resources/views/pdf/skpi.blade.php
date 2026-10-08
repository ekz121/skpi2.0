<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SKPI {{ $snapshot['nim'] }}</title>
    <style>
        @page { margin: 34px 42px 48px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 9px; line-height: 1.45; }
        .letterhead { display: table; width: 100%; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 16px; }
        .letterhead img { display: table-cell; width: 58px; vertical-align: middle; }
        .letterhead div { display: table-cell; text-align: center; vertical-align: middle; }
        .letterhead strong { display: block; font-size: 16px; letter-spacing: .4px; }
        .letterhead span { display: block; font-size: 8px; margin-top: 4px; }
        h1 { font-size: 13px; text-align: center; margin: 10px 0 2px; }
        .number { text-align: center; margin-bottom: 16px; }
        .intro { text-align: justify; }
        h2 { font-size: 10px; text-transform: uppercase; background: #e5e7eb; padding: 6px 8px; margin: 16px 0 8px; page-break-after: avoid; }
        h3 { font-size: 9px; margin: 10px 0 5px; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #98a2b3; padding: 5px 6px; vertical-align: top; }
        th { background: #f2f4f7; text-align: left; }
        .identity td:first-child { width: 31%; font-weight: bold; background: #f8fafc; }
        ul { margin: 4px 0 8px; padding-left: 18px; }
        li { margin-bottom: 3px; }
        .signature { width: 42%; margin-left: auto; margin-top: 28px; page-break-inside: avoid; }
        .signature-space { height: 52px; }
        .muted { color: #667085; }
    </style>
</head>
<body>
    <header class="letterhead"><img src="{{ public_path('assets/polteksi-logo-official.png') }}" alt=""><div><strong>POLITEKNIK SEMEN INDONESIA</strong><span>Jalan R.A. Kartini Kompleks Pabrik Semen PT Semen Indonesia No. 25C, Kesemen, Sukorame, Kecamatan Gresik, Kabupaten Gresik, Jawa Timur 61111<br>SK Pendirian No. 312/M/2018 tanggal 20 Juli 2018</span></div></header>
    <h1>SURAT KETERANGAN PENDAMPING IJAZAH (SKPI)</h1>
    <div class="number">Nomor: {{ $number }}</div>
    <p class="intro">Surat Keterangan Pendamping Ijazah (SKPI) ini diterbitkan sebagai dokumen pelengkap Ijazah yang menerangkan capaian pembelajaran dan kompetensi dari pemegang ijazah, sesuai dengan Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi tentang Surat Keterangan Pendamping Ijazah.</p>

    <h2>Informasi tentang identitas pemilik SKPI</h2>
    <table class="identity">
        <tr><td>Nama lengkap</td><td>{{ $snapshot['name'] }}</td></tr>
        <tr><td>Tempat dan tanggal lahir</td><td>{{ $snapshot['birthplace'] }}, {{ \Carbon\Carbon::parse($snapshot['birthdate'])->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Nomor induk mahasiswa</td><td>{{ $snapshot['nim'] }}</td></tr>
        <tr><td>Tahun masuk</td><td>{{ $snapshot['cohort'] }}</td></tr>
        <tr><td>Tahun lulus</td><td>{{ $snapshot['graduation_year'] ?: '[DATA RESMI BELUM TERSEDIA]' }}</td></tr>
        <tr><td>Nomor ijazah nasional</td><td>{{ $snapshot['diploma_number'] ?: '[DATA RESMI BELUM TERSEDIA]' }}</td></tr>
        <tr><td>Gelar akademik</td><td>{{ $snapshot['academic_title'] ?: '[DATA RESMI BELUM TERSEDIA]' }}</td></tr>
    </table>

    <h2>Informasi tentang kualifikasi dan hasil yang dicapai</h2>
    <h3>A. Capaian Pembelajaran</h3>
    @forelse($snapshot['learning_outcomes'] ?? [] as $group => $outcomes)
        <h3>{{ $loop->iteration }}. {{ $group }}</h3><ul>@foreach($outcomes as $outcome)<li>{{ $outcome }}</li>@endforeach</ul>
    @empty
        <p class="muted">[CAPAIAN PEMBELAJARAN RESMI PROGRAM STUDI BELUM DIKONFIGURASI]</p>
    @endforelse

    <h2>Informasi aktivitas, prestasi, dan sertifikasi</h2>
    <p>Bagian ini berisi rekam jejak mahasiswa di luar kurikulum utama selama masa perkuliahan.</p>
    @php
        $activityGroups = ['A. Sertifikasi Kompetensi / Profesi' => [], 'B. Prestasi dan Penghargaan' => [], 'C. Pengalaman Organisasi & Kerja Praktik' => []];
        foreach ($snapshot['activities'] as $activity) {
            $isInternship = str_contains(Str::lower($activity['activity_type']), 'magang') || str_contains(Str::lower($activity['activity_type']), 'pkl');
            if ($activity['category'] === 'Prestasi Akademik dan Nonakademik' || $activity['category'] === 'Proyek, Penelitian, dan Pengabdian Masyarakat') $activityGroups['B. Prestasi dan Penghargaan'][] = $activity;
            elseif ($activity['category'] === 'Organisasi dan Kepemimpinan' || $isInternship) $activityGroups['C. Pengalaman Organisasi & Kerja Praktik'][] = $activity;
            else $activityGroups['A. Sertifikasi Kompetensi / Profesi'][] = $activity;
        }
    @endphp
    @foreach($activityGroups as $group => $activities)
        <h3>{{ $group }}</h3>
        <table><thead><tr><th style="width:5%">No</th><th>Nama Kegiatan</th><th>Penyelenggara</th><th style="width:10%">Tahun</th><th style="width:12%">Bobot Nilai</th></tr></thead><tbody>@forelse($activities as $activity)<tr><td>{{ $loop->iteration }}</td><td>{{ $activity['name'] }}</td><td>{{ $activity['organizer'] }}</td><td>{{ $activity['year'] }}</td><td>{{ $activity['points'] }}</td></tr>@empty<tr><td></td><td></td><td></td><td></td><td></td></tr>@endforelse</tbody></table>
    @endforeach

    <h2>Pengesahan SKPI</h2>
    <p>Surat Keterangan Pendamping Ijazah ini dinyatakan sah dan berlaku sebagai dokumen resmi pendamping ijazah, serta dibuat berdasarkan data yang benar untuk dipergunakan sebagaimana mestinya.</p>
    <div class="signature"><p>Gresik, {{ \Carbon\Carbon::parse($snapshot['issued_date'])->locale('id')->translatedFormat('d F Y') }}<br>Wakil Direktur I Bidang Akademik</p><div class="signature-space"></div><strong>{{ config('skpi.signatory_name') }}</strong><br><span class="muted">NIDN. {{ config('skpi.signatory_nidn') }}</span></div>
</body>
</html>
