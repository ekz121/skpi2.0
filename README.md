# SKEM dan SKPI Politeknik Semen Indonesia

Aplikasi Laravel untuk pencatatan kegiatan mahasiswa, pengajuan SKPI, serta penerbitan Word dan PDF dari template resmi.

## Teknologi

- Laravel 12 dan PHP 8.2+
- MySQL sebagai database utama
- Blade, CSS, dan JavaScript tanpa dependensi frontend eksternal
- Template DOCX resmi dan LibreOffice untuk konversi PDF yang konsisten
- Dompdf sebagai renderer PDF cadangan

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`.
2. Buat database MySQL bernama `skem_skpi_polteksi`.
3. Sesuaikan koneksi `DB_*` di `.env`.
4. Jalankan perintah berikut:

```powershell
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Jika Composer belum tersedia global di mesin ini, gunakan `php .tools/composer.phar install`.

## Akun demonstrasi

- Mahasiswa: `mahasiswa@demo.polteksi.ac.id` / `demo12345`
- Admin: `admin@polteksi.ac.id` / `polteksi123`

Identitas, kegiatan, dan angka akun demo ditandai sebagai data simulasi di antarmuka.

## Konfigurasi penerbitan resmi

Sebelum penggunaan produksi:

- set `SKPI_DEMO_MODE=false`;
- isi `SKPI_SIGNATORY_NAME` dan `SKPI_SIGNATORY_NIDN`;
- isi `SKPI_OFFICE_BINARY` bila LibreOffice tidak berada di lokasi instalasi standar;
- lengkapi capaian pembelajaran resmi seluruh program studi;
- pastikan tahun lulus, nomor ijazah, dan gelar akademik tersedia;
- konfigurasi email dan penyimpanan privat kampus.

Sistem menolak penerbitan jika data wajib tersebut belum lengkap. Sertifikat baru langsung tersimpan tanpa menunggu verifikasi admin, dan satu sertifikat sudah cukup untuk mengirim permintaan SKPI.

## Pengujian

Jalankan `php artisan test`. Pengujian mencakup role, unggahan langsung, isolasi data, keputusan tunggal dan massal, render halaman, serta penerbitan Word/PDF.

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
