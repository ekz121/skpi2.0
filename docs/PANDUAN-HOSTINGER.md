# Panduan hosting di subdomain Hostinger

Panduan ini memakai MySQL, upload ZIP melalui File Manager, dan SSH untuk perintah Laravel. Struktur proyek sudah menyertakan `.htaccess` akar yang meneruskan permintaan ke folder `public`.

## 1. Persiapan di hPanel

1. Buat subdomain, misalnya `skpi.domainanda.id`.
2. Buat database MySQL dan pengguna database dari menu **Databases**.
3. Catat host, port, nama database, nama pengguna, dan password database.
4. Aktifkan SSH dari menu **Websites > Manage > Advanced > SSH Access**.
5. Pastikan subdomain menggunakan PHP 8.2 atau lebih baru serta mengaktifkan ekstensi DOM, Fileinfo, dan ZIP.

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
MAIL_SCHEME=null
MAIL_FROM_ADDRESS=alamat_email
MAIL_FROM_NAME="SKEM Polteksi"

SKPI_SIGNATORY_NAME="Nama Pejabat"
SKPI_SIGNATORY_NIDN="NIDN Pejabat"
```

Gunakan kredensial database dan SMTP dari hPanel. Nilai `SKPI_SIGNATORY_NAME` dan `SKPI_SIGNATORY_NIDN` wajib agar admin dapat menerbitkan Word.

Untuk SMTP port 587, biarkan `MAIL_SCHEME=null`; library email akan memakai STARTTLS ketika server mendukungnya. Jika penyedia email secara khusus memberi port 465 dengan TLS implisit, gunakan `MAIL_SCHEME=smtps`.

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

## 6. Memasang revisi ini pada situs yang sudah online

Gunakan langkah ini satu kali saat memperbarui versi lama ke revisi registrasi 77 mahasiswa:

1. Buat backup file dan database dari menu **Backups** di hPanel.
2. Jangan hapus `.env`, `storage/app`, atau database lama. Folder `storage/app` menyimpan sertifikat dan Word yang sudah dibuat.
3. Aktifkan mode pemeliharaan melalui SSH:

```bash
cd domains/nama-domain/public_html
php artisan down --retry=60
```

4. Upload ZIP kode terbaru melalui File Manager, lalu ekstrak dengan pilihan menimpa file lama. ZIP tidak boleh berisi `.env` dari komputer lokal.
5. Jalankan:

```bash
composer2 install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Perintah `db:seed` pada langkah ini dibutuhkan untuk memasukkan 77 data akademik dan sembilan akun uji. Untuk pembaruan setelah revisi ini, jangan ulangi seeder kecuali catatan rilis secara khusus memintanya.

## 7. Pembaruan rutin setelah revisi ini

Ada dua cara yang dapat dipakai.

### Cara A: Git dari hPanel

Jika repository sudah dihubungkan melalui menu **Git** di hPanel, pilih branch `main`, lalu tekan **Deploy** setelah perubahan di-push. Hostinger juga menyediakan URL webhook untuk auto-deployment. Sesudah deploy, masuk melalui SSH dan jalankan:

```bash
cd domains/nama-domain/public_html
php artisan down --retry=60
composer2 install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Jangan menyiapkan deployment Git baru langsung ke `public_html` yang sudah berisi situs. Panduan Hostinger mensyaratkan folder tujuan kosong saat repository pertama kali ditambahkan. Untuk situs yang sudah dipasang manual, tetap gunakan cara ZIP atau siapkan Git dengan bantuan backup dan pemindahan terencana.

### Cara B: ZIP melalui File Manager

1. Buat backup file dan database di hPanel.
2. Jalankan `php artisan down --retry=60` melalui SSH.
3. Upload ZIP kode baru ke `public_html`, lalu ekstrak dan timpa file kode lama.
4. Jangan menghapus atau menimpa `.env` dan isi `storage/app`.
5. Jalankan rangkaian perintah yang sama seperti Cara A, mulai dari `composer2 install` sampai `php artisan up`.
6. Buka situs dalam jendela privat dan uji login, unggah sertifikat, pengajuan SKPI, serta unduh Word.

Gunakan `composer install`, bukan `composer update`, agar versi paket mengikuti `composer.lock`. Jangan menjalankan `php artisan migrate:fresh` pada produksi karena seluruh tabel dan data akan dihapus.

Jika pembaruan gagal, jalankan `php artisan up` supaya situs dapat dibuka kembali, lalu pulihkan file dan database dari backup dengan tanggal yang sama.

## 8. Pemecahan masalah singkat

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
- Deployment Git: https://support.hostinger.com/en/articles/1583302-how-to-deploy-a-git-repository
- Restore backup: https://support.hostinger.com/en/articles/4283700-how-to-restore-backups-at-hostinger
