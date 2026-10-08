# SKEM dan SKPI Politeknik Semen Indonesia

Aplikasi Laravel untuk registrasi mahasiswa dari data akademik terkontrol, pencatatan sertifikat, pengajuan SKPI, serta penerbitan dokumen Word dari template resmi.

## Teknologi

- Laravel 12 dan PHP 8.2 atau lebih baru
- MySQL
- Blade, CSS, dan JavaScript tanpa dependensi frontend eksternal
- Template DOCX resmi untuk hasil SKPI

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`.
2. Buat database MySQL dan sesuaikan nilai `DB_*`.
3. Jalankan:

```powershell
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Akun

Admin: `admin@polteksi.ac.id` / `pastikerja123`

Sembilan akun uji memakai password `demo12345`:

| Email | Nama |
| --- | --- |
| `demo01@demo.polteksi.ac.id` | Mahasiswa Uji Alfa |
| `demo02@demo.polteksi.ac.id` | Mahasiswa Uji Bima |
| `demo03@demo.polteksi.ac.id` | Mahasiswa Uji Citra |
| `demo04@demo.polteksi.ac.id` | Mahasiswa Uji Damar |
| `demo05@demo.polteksi.ac.id` | Mahasiswa Uji Elin |
| `demo06@demo.polteksi.ac.id` | Mahasiswa Uji Faris |
| `demo07@demo.polteksi.ac.id` | Mahasiswa Uji Gina |
| `demo08@demo.polteksi.ac.id` | Mahasiswa Uji Hadi |
| `demo09@demo.polteksi.ac.id` | Mahasiswa Uji Intan |

## Alur utama

- Registrasi memilih nama dari 77 data sumber. NIM, prodi, angkatan 2023, tahun lulus 2026, nomor ijazah, dan gelar terisi otomatis.
- Sertifikat langsung aktif setelah diunggah. Mahasiswa dan admin dapat melihat, mengubah, dan menghapus sertifikat.
- Satu sertifikat cukup untuk mengajukan SKPI. Mahasiswa hanya menambahkan tempat dan tanggal lahir.
- Admin dapat mencari permintaan, menerbitkan atau menolak satu maupun banyak permintaan, dan mengunduh ZIP berisi Word.
- Jika sertifikat yang mendasari SKPI diubah atau dihapus, dokumen lama dibatalkan agar mahasiswa dapat mengunggah dan mengajukan ulang.

## Konfigurasi produksi

Isi `SKPI_SIGNATORY_NAME` dan `SKPI_SIGNATORY_NIDN`, koneksi database, SMTP, `APP_URL`, dan `APP_TIMEZONE=Asia/Jakarta` pada `.env`. Berkas sertifikat dan Word disimpan pada disk lokal privat.

Panduan lengkap:

- [Struktur database](docs/STRUKTUR-DATABASE.md)
- [Hosting di subdomain Hostinger](docs/PANDUAN-HOSTINGER.md)

## Pengujian

Jalankan `php artisan test`. Pengujian meliputi registrasi terkontrol, 77 data sumber, role, unggahan otomatis, CRUD sertifikat, pencarian, pengajuan ulang, keputusan massal, Word, dan isolasi data.

<details>
<summary>Catatan framework Laravel</summary>

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

</details>
