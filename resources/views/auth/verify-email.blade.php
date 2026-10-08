@extends('layouts.auth')
@section('title', 'Verifikasi Email')
@section('content')
    <div class="auth-heading"><p class="section-kicker">Satu langkah lagi</p><h2>Periksa email Anda</h2><p>Kami mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Akun belum dapat mengajukan kegiatan sebelum email terverifikasi.</p></div>
    <div class="verification-note">Tautan memiliki masa berlaku. Folder spam juga perlu diperiksa jika email belum terlihat.</div>
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button button-primary button-block" type="submit">Kirim ulang tautan</button></form>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-quiet button-block" type="submit">Keluar dari akun</button></form>
@endsection
