# Panduan hosting di subdomain Hostinger

Panduan ini memakai MySQL, upload ZIP melalui File Manager, dan SSH untuk perintah Laravel. Struktur proyek sudah menyertakan `.htaccess` akar yang meneruskan permintaan ke folder `public`.

## 1. Persiapan di hPanel

1. Buat subdomain, misalnya `skpi.domainanda.id`.
2. Buat database MySQL dan pengguna database dari menu **Databases**.
3. Catat host, port, nama database, nama pengguna, dan password database.
4. Aktifkan SSH dari menu **Websites > Manage > Advanced > SSH Access**.
5. Pastikan subdomain menggunakan PHP 8.2 atau lebih baru.

## 2. Membuat ZIP untuk diunggah

Dari folder proyek di komputer jalankan:

```powershell
composer install --no-dev --optimize-autoloader
Compress-Archive -Path * -DestinationPath skpi-hostinger.zip -Force
```

Pastikan ZIP berisi folder `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, `vendor`, serta file `.htaccess` dan `artisan`. Jangan masukkan `.env` lokal ke ZIP.

## 3. Upload dan ekstrak

1. Buka File Manager subdomain.
2. Masuk ke document root subdomain, biasanya `domains/nama-domain/public_html`.
3. Upload `skpi-hostinger.zip` langsung ke folder tersebut.
4. Ekstrak ZIP. Pastikan `artisan` berada langsung di `public_html`, bukan di folder bertingkat seperti `public_html/skpi-hostinger`.
5. Hapus file ZIP setelah ekstraksi berhasil.

## 4. Instal dependensi dan konfigurasi

Masuk melalui SSH, pindah ke document root subdomain, lalu jalankan:

```bash
composer2 install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env` dengan nilai produksi:

```dotenv
APP_NAME="SKEM dan SKPI Polteksi"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://skpi.domainanda.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=host_mysql_dari_hpanel
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=host_smtp
MAIL_PORT=587
MAIL_USERNAME=alamat_email
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=alamat_email
MAIL_FROM_NAME="SKEM Polteksi"

SKPI_SIGNATORY_NAME="Nama Pejabat"
SKPI_SIGNATORY_NIDN="NIDN Pejabat"
```

Gunakan kredensial database dan SMTP dari hPanel. Nilai `SKPI_SIGNATORY_NAME` dan `SKPI_SIGNATORY_NIDN` wajib agar admin dapat menerbitkan Word.

## 5. Membuat tabel dan data awal

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Atur izin folder tulis bila diperlukan:

```bash
chmod -R 775 storage bootstrap/cache
```

Setelah itu buka subdomain, masuk sebagai admin, lalu uji registrasi, unggah sertifikat, pengajuan SKPI, penerbitan Word, dan unduh ZIP Word.

## 6. Pembaruan berikutnya

Sebelum memperbarui situs, buat backup file dan database dari hPanel. Upload serta ekstrak versi baru, lalu jalankan:

```bash
composer2 install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jangan menjalankan `php artisan migrate:fresh` pada produksi.

## 7. Pemecahan masalah singkat

- Halaman 500: periksa `storage/logs/laravel.log`, koneksi database, `APP_KEY`, dan izin `storage`.
- Halaman tanpa CSS: pastikan `APP_URL` memakai URL HTTPS subdomain dan isi folder `public/assets` terunggah.
- Tautan kembali ke domain utama: jalankan kembali `php artisan optimize:clear` setelah mengubah `.env`.
- Word gagal diterbitkan: periksa nama dan NIDN penandatangan, profil mahasiswa, serta keberadaan `resources/templates/skpi-template.docx`.
- Registrasi kosong: jalankan `php artisan db:seed --force`, lalu periksa jumlah `student_registries`.

Referensi Hostinger yang digunakan:

- Upload website manual: https://support.hostinger.com/en/articles/1583289-how-to-manually-transfer-a-website-to-hostinger
- Deployment Laravel: https://support.hostinger.com/en/articles/6152127-how-to-deploy-laravel-8-at-hostinger
- Composer: https://support.hostinger.com/en/articles/5792078-how-to-use-composer
- SSH: https://support.hostinger.com/en/articles/1583645-how-to-enable-ssh-access
