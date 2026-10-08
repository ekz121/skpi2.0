@extends('layouts.app')
@section('title', 'Panduan SKEM')
@section('content')
    <div class="page-heading heading-with-action"><div><p class="section-kicker">Pedoman kegiatan</p><h1>Panduan poin SKEM</h1><p>Bobot tetap ditampilkan sebagai informasi kategori kegiatan. Pengajuan SKPI dapat dilakukan setelah satu sertifikat tersimpan.</p></div><label class="search-field">@include('partials.icon', ['name' => 'search'])<input type="search" data-guide-search placeholder="Cari kegiatan atau kategori"></label></div>
    <div class="guide-notice"><strong>Aturan utama</strong><p>Poin hanya dihitung setelah pengajuan disetujui. Bukti harus resmi, dapat dibaca, dan memuat identitas serta informasi kegiatan yang diperlukan.</p></div>
    <div class="guide-groups" data-guide-list>
        @foreach($rules as $category => $items)
            <section class="panel guide-section" data-guide-item>
                <div class="panel-head"><div><h2>{{ $category }}</h2><p>{{ $items->count() }} ketentuan poin</p></div></div>
                <div class="table-wrap"><table><thead><tr><th>Jenis kegiatan</th><th>Tingkat / capaian</th><th>Bukti</th><th>Poin</th></tr></thead><tbody>
                    @foreach($items as $rule)<tr><td><strong>{{ $rule->activity_type }}</strong><small>{{ $rule->subcategory }}</small></td><td>{{ collect([$rule->level, $rule->achievement, $rule->duration_label])->filter()->join(' · ') ?: 'Sesuai jenis kegiatan' }}</td><td>{{ $rule->evidence_label }} @if($rule->is_mandatory)<span class="mandatory-label">Wajib</span>@endif</td><td><strong>{{ $rule->points }}</strong></td></tr>@endforeach
                </tbody></table></div>
            </section>
        @endforeach
        <div class="empty-state guide-empty" hidden><h3>Kegiatan tidak ditemukan</h3><p>Coba gunakan kata kunci lain.</p></div>
    </div>
@endsection
