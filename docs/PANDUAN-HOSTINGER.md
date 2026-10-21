# Panduan Deploy SKEM dan SKPI POLTEKSI ke Hostinger

Panduan ini dibuat untuk proyek pada domain `https://skpi.polteksi.ac.id` dengan database MySQL `u841207907_skpi`. Gunakan bagian A untuk Shared atau Cloud Hosting melalui hPanel tanpa SSH. Gunakan bagian B jika paket hosting menyediakan SSH. Bagian C khusus VPS.

## 1. Informasi proyek

Proyek menggunakan:

- Laravel 12 dan PHP minimal 8.2.
- MySQL dengan zona waktu aplikasi `Asia/Jakarta`.
- Dokumen keluaran Word saja.
- Template resmi di `resources/templates/skpi-template.docx`.
- Bukti sertifikat dan dokumen Word di penyimpanan privat `storage/app/private`.
- Aset antarmuka di `public/assets`.
- Queue produksi `sync`, jadi versi saat ini tidak membutuhkan queue worker.
- Tidak ada jadwal aplikasi aktif di `routes/console.php`, jadi versi saat ini tidak membutuhkan cron job.

Database yang sudah dibuat:

```text
Nama database : u841207907_skpi
User database : u841207907_skpi
Domain        : https://skpi.polteksi.ac.id
```

Password database tidak ditulis dalam proyek atau panduan. Ambil dari hPanel dan simpan hanya di `.env` pada server.

## 2. Persiapan sebelum upload

### 2.1 Buat cadangan

Jika situs sudah pernah digunakan:

1. Buka hPanel, lalu buka File Manager.
2. Unduh folder `skpi_app/storage/app/private` sebagai cadangan bukti sertifikat dan dokumen Word.
3. Unduh file `skpi_app/.env`.
4. Buka phpMyAdmin, pilih database `u841207907_skpi`, lalu pilih Export dan simpan hasil SQL.
5. Jangan mengimpor `database/skpi-hostinger.sql` ke database aktif. File itu menghapus tabel sebelum membuat ulang.

### 2.2 Siapkan kode dan aset di komputer

Untuk perubahan yang memakai Vite, jalankan:

```powershell
npm ci
npm run build
```

Tampilan proyek saat ini memuat `public/assets/app.css` dan `public/assets/app.js` secara langsung. Pastikan kedua file tersebut ikut dalam paket. Hasil Vite di `public/build` tetap boleh disertakan jika proyek dikembangkan lebih lanjut.

Pasang dependensi PHP produksi:

```powershell
composer install --no-dev --optimize-autoloader
```

Jika memakai paket ZIP yang sudah dibuat oleh proyek, `vendor` sudah disertakan dan langkah Composer tidak perlu dijalankan di Hostinger.

### 2.3 Buat paket tanpa SSH

Jalankan dari PowerShell pada folder proyek:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy\build-hostinger-no-ssh.ps1
```

Hasilnya adalah `skpi-hostinger-tanpa-ssh.zip` dengan struktur:

```text
public_html/
skpi_app/
database-import/
PANDUAN-HOSTINGER.md
```

Folder `public_html` hanya berisi file yang boleh diakses browser. Folder `skpi_app` menyimpan kode, vendor, template Word, dan storage. Paket menyertakan `.env.hostinger.example`, bukan `.env`, supaya pembaruan tidak dapat menimpa kunci dan kredensial produksi.

## A. Deploy melalui hPanel tanpa SSH

### A.1 Atur versi PHP dan ekstensi

1. Buka hPanel.
2. Pilih Websites, lalu Dashboard untuk `skpi.polteksi.ac.id`.
3. Buka PHP Configuration.
4. Pilih PHP 8.2 atau versi 8.x yang lebih baru dan kompatibel.
5. Pastikan ekstensi berikut aktif:

```text
ctype
curl
dom
fileinfo
filter
hash
mbstring
openssl
pcre
pdo
pdo_mysql
session
tokenizer
xml
zip
```

Ekstensi `zip` diperlukan untuk membaca dan menghasilkan dokumen Word. Ekstensi `dom` dan `xml` diperlukan untuk mengisi template Word.

### A.2 Perbaiki folder `public_html` yang menjadi tautan rusak

Pada gambar lama, `public_html → skpi_app/public` berstatus `invalid link`. Itu berarti target tautannya sudah hilang.

Lakukan langkah berikut:

1. Pastikan cadangan sudah dibuat jika sebelumnya ada file penting.
2. Hapus hanya item `public_html` yang berstatus `invalid link`.
3. Jangan hapus database di hPanel.
4. Jangan hapus folder aplikasi lama sebelum isinya dicadangkan.
5. Buat folder biasa bernama `public_html` jika proses ekstrak ZIP tidak membuatnya otomatis.

### A.3 Upload dan ekstrak paket

1. Buka File Manager pada folder utama akun, yaitu lokasi yang menampilkan `public_html`.
2. Upload `skpi-hostinger-tanpa-ssh.zip` ke lokasi tersebut.
3. Klik kanan file ZIP, lalu pilih Extract.
4. Setelah ekstrak, pastikan `public_html` dan `skpi_app` berada sejajar.
5. Hapus ZIP dari server setelah semua pemeriksaan selesai untuk menghemat ruang.

Struktur yang benar:

```text
/home/u841207907/
├── public_html/
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   └── favicon.ico
└── skpi_app/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── storage/
    ├── vendor/
    ├── artisan
    └── .env
```

Jangan menaruh seluruh Laravel di `public_html`. File `.env`, template, dan storage harus tetap berada di `skpi_app`.

### A.4 Periksa `public_html/index.php`

Paket sudah memakai jalur berikut:

```php
require __DIR__.'/../skpi_app/vendor/autoload.php';
$app = require_once __DIR__.'/../skpi_app/bootstrap/app.php';
$app->usePublicPath(__DIR__);
```

Jika nama folder aplikasi diubah, sesuaikan tiga jalur yang mengandung `skpi_app`.

### A.5 Periksa `.htaccess`

File `public_html/.htaccess` harus berisi aturan front controller Laravel. Paket sudah menyertakan file dari `public/.htaccess`. Aktifkan Show hidden files di File Manager jika `.htaccess` tidak terlihat.

Jangan menambahkan pengalihan ke folder `/public`, karena document root sudah berupa `public_html`.

### A.6 Atur `.env` produksi

Untuk instalasi pertama, ubah nama `skpi_app/.env.hostinger.example` menjadi `skpi_app/.env`, lalu isi semua nilai `GANTI_...`. Untuk situs yang sudah aktif, pertahankan `.env` lama dan jangan menggantinya dengan file contoh.

Edit `skpi_app/.env`:

```dotenv
APP_NAME="SKEM dan SKPI Polteksi"
APP_ENV=production
APP_KEY=base64:KUNCI_YANG_SUDAH_DIBUAT
APP_DEBUG=false
APP_URL=https://skpi.polteksi.ac.id
APP_TIMEZONE=Asia/Jakarta

APP_LOCALE=id
APP_FALLBACK_LOCALE=id

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u841207907_skpi
DB_USERNAME=u841207907_skpi
DB_PASSWORD="PASSWORD_DATABASE_DARI_HPANEL"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=alamat-email-pengirim@polteksi.ac.id
MAIL_PASSWORD="PASSWORD_EMAIL"
MAIL_FROM_ADDRESS=alamat-email-pengirim@polteksi.ac.id
MAIL_FROM_NAME="SKEM dan SKPI Polteksi"

SKPI_DEMO_MODE=false
SKPI_SIGNATORY_NAME="NAMA PEJABAT PENANDATANGAN"
SKPI_SIGNATORY_NIDN="NIDN PEJABAT"
SKPI_INSTITUTION_ACCREDITATION=""
```

Catatan penting:

- File `.env.hostinger.example` pada paket pertama memiliki `APP_KEY` acak. Setelah `.env` produksi dibuat, jangan pernah mengganti `APP_KEY` saat pembaruan karena sesi, tautan verifikasi bertanda tangan, dan data terenkripsi lama dapat menjadi tidak berlaku.
- Jangan memakai `APP_DEBUG=true` pada produksi.
- Jika hPanel menampilkan host MySQL selain `localhost`, gunakan nilai dari menu Databases.
- Nilai nama dan NIDN pejabat harus diisi agar Word dapat diterbitkan.

### A.7 Isi database

Pilih satu kondisi berikut.

#### Instalasi baru dan database masih kosong

1. Buka phpMyAdmin dari database `u841207907_skpi`.
2. Pilih database tersebut.
3. Buka Import.
4. Pilih `database-import/skpi-hostinger.sql` dari hasil ekstrak, atau file lokal `database/skpi-hostinger.sql`.
5. Gunakan format SQL dan character set UTF-8.
6. Jalankan impor.
7. Pastikan terdapat tabel `users`, `faculties`, `student_registries`, `submissions`, `skpi_requests`, `skpi_withdrawals`, `sessions`, dan tabel lainnya.

File penuh berisi struktur tabel, 77 data registrasi mahasiswa, aturan kegiatan, akun admin, dan akun demo. File ini mengandung `DROP TABLE`, jadi hanya untuk instalasi baru atau pemulihan penuh yang memang disengaja.

#### Situs sudah aktif dan data harus dipertahankan

1. Buat export database sebelum perubahan.
2. Upload kode baru tanpa menimpa `.env` dan `storage/app/private`.
3. Jika pembaruan penarikan SKPI belum pernah diterapkan, impor `database/update-2026-10-14.sql` satu kali.
4. Setelah itu impor `database/update-2026-10-15.sql` satu kali untuk fitur manajemen user.
5. Impor `database/update-2026-10-19.sql` satu kali untuk menambah Sertifikat Magang, Sertifikat Lain-lain, dan kolom jenis kegiatan bebas.
6. Impor `database/update-2026-10-21.sql` satu kali untuk menautkan akun mahasiswa lama ke daftar akademik berdasarkan NIM. Pembaruan ini tidak menghapus data.
7. Pastikan tabel `skpi_withdrawals` dan `faculties` muncul.
8. Pastikan `users` memiliki kolom `faculty_id`, `study_program_id`, `is_active`, `deactivated_at`, dan `deactivated_by`.
9. Pastikan `submissions` memiliki kolom `custom_activity_type`.
10. Buka menu **Data Mahasiswa**. Angka daftar akademik harus 77; angka sudah mendaftar mengikuti akun produksi yang benar-benar telah dibuat.
11. Buka **Manajemen User** dan uji pencarian, reset password, serta aktivasi akun.
12. Jangan mengimpor SQL penuh ke database aktif.

File pembaruan harus diimpor satu kali dan secara berurutan. File 15 Oktober menambah kolom dengan `ALTER TABLE`, sehingga impor ulang akan menghasilkan pesan kolom sudah ada. Tetap lakukan backup sebelum setiap impor.

Jika pembaruan berikutnya menyediakan file `database/update-YYYY-MM-DD.sql`, impor file secara urut berdasarkan tanggal.

### A.8 Permission folder

Atur permission berikut melalui File Manager jika tersedia:

```text
Folder umum                         755
File umum                           644
skpi_app/storage                    775
skpi_app/bootstrap/cache            775
skpi_app/storage/app/private        775
skpi_app/storage/framework          775
skpi_app/storage/logs               775
```

Jangan memakai permission `777` kecuali dukungan Hostinger menyuruh sementara untuk diagnosis.

Proyek menyimpan bukti secara privat dan mengunduhnya melalui controller yang memeriksa akun. Karena itu, versi saat ini tidak membutuhkan `storage:link` untuk bukti sertifikat atau Word. Jalankan `storage:link` hanya jika nanti ada file publik di `storage/app/public`.

### A.9 SMTP dan verifikasi email

1. Buat mailbox pengirim di Hostinger Email atau gunakan SMTP resmi institusi.
2. Masukkan host, port, username, dan password ke `.env`.
3. Untuk Hostinger SMTP dengan SSL, gunakan port 465 dan `MAIL_SCHEME=smtps`.
4. Jika penyedia memakai STARTTLS, biasanya gunakan port 587 dan sesuaikan skema berdasarkan petunjuk penyedia.
5. Daftar satu akun uji dengan email yang dapat diakses.
6. Masuk sebagai admin dan tekan **Kirim email uji ke admin** pada Dashboard.
7. Jika server SMTP menerima email tetapi kotak masuk belum menerimanya, periksa folder spam serta konfigurasi SPF, DKIM, dan DMARC domain pada penyedia DNS/email.
8. Daftar satu mahasiswa uji, buka tautan verifikasi dari perangkat lain, masuk dengan akun yang sama, lalu pastikan proses kembali ke tautan dan email menjadi terverifikasi.
9. Pastikan semua tautan email memakai `https://skpi.polteksi.ac.id`.

Pengiriman dari aplikasi sampai server SMTP dapat diuji, tetapi tidak ada aplikasi yang dapat menjamin email selalu masuk ke inbox karena keputusan akhir juga ditentukan penyedia email penerima dan reputasi/domain pengirim.

### A.10 Domain dan SSL

1. Pastikan DNS subdomain `skpi.polteksi.ac.id` diarahkan ke hosting yang benar.
2. Buka Security, lalu SSL di hPanel.
3. Aktifkan sertifikat SSL untuk subdomain.
4. Aktifkan Force HTTPS setelah sertifikat berstatus aktif.
5. Pastikan `APP_URL=https://skpi.polteksi.ac.id` tanpa garis miring di akhir.

### A.11 Cache tanpa SSH

Paket baru sebaiknya tidak membawa cache konfigurasi dari komputer lokal. Jika nilai `.env` tidak terbaca:

1. Buka `skpi_app/bootstrap/cache`.
2. Hapus file cache `.php` hasil optimasi, tetapi jangan hapus foldernya.
3. Buka `skpi_app/storage/framework/views`.
4. Hapus file kompilasi Blade jika tampilan lama masih muncul.
5. Muat ulang situs.

## B. Deploy Shared atau Cloud Hosting dengan SSH

Setelah file diupload dan `.env` diatur, masuk ke folder aplikasi:

```bash
cd ~/skpi_app
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Untuk instalasi baru yang memang perlu data awal:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Jangan menjalankan seeder pada database aktif jika akun demo dan data awal tidak ingin dibuat ulang. Migrasi tidak mengganti data sertifikat yang sudah ada.

Jika `storage:link` gagal dan aplikasi hanya memakai unduhan privat, kegagalan itu tidak menghambat bukti sertifikat atau Word.

## C. Deploy pada VPS

### C.1 Paket server

Contoh Ubuntu dengan Nginx:

```bash
sudo apt update
sudo apt install nginx mysql-server git unzip composer php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-fileinfo
```

Hostinger juga menyediakan template VPS Laravel dengan CloudPanel. Memasang ulang operating system pada VPS menghapus data server, jadi gunakan hanya pada VPS baru atau setelah backup penuh.

### C.2 Upload aplikasi

```bash
sudo mkdir -p /var/www/skpi
sudo chown -R "$USER":www-data /var/www/skpi
git clone https://github.com/ekz121/skpi2.0 /var/www/skpi
cd /var/www/skpi
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env` memakai nilai produksi pada bagian A.6, lalu:

```bash
php artisan migrate --force
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### C.3 Konfigurasi Nginx

Gunakan document root `/var/www/skpi/public`:

```nginx
server {
    listen 80;
    server_name skpi.polteksi.ac.id;
    root /var/www/skpi/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi, uji, lalu muat ulang Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/skpi /etc/nginx/sites-enabled/skpi
sudo nginx -t
sudo systemctl reload nginx
```

Pasang SSL melalui CloudPanel atau Certbot setelah DNS sudah mengarah ke VPS.

### C.4 Queue dan cron pada VPS

Versi produksi ini memakai `QUEUE_CONNECTION=sync`, jadi Supervisor tidak diperlukan. Jika nanti diubah ke `database`, buat worker yang menjalankan:

```bash
php /var/www/skpi/artisan queue:work --sleep=3 --tries=3 --timeout=120
```

Kelola worker dengan Supervisor dan jalankan `php artisan queue:restart` setiap selesai update.

Saat aplikasi mulai memiliki jadwal di `routes/console.php`, tambahkan satu cron:

```cron
* * * * * cd /var/www/skpi && php artisan schedule:run > /dev/null 2>&1
```

Versi saat ini tidak mempunyai jadwal, jadi cron tersebut belum wajib.

## 3. Cara update situs berikutnya

### 3.1 Update tanpa SSH

1. Export database dan cadangkan `skpi_app/.env` serta `skpi_app/storage/app/private`.
2. Ekstrak paket baru ke folder sementara, misalnya `update_skpi`.
3. Salin kode baru ke `skpi_app`, tetapi pertahankan `.env` lama.
4. Jangan menghapus atau mengganti `storage/app/private` karena berisi bukti dan Word milik pengguna.
5. Ganti isi `public_html` dengan versi baru, termasuk `assets`, `.htaccess`, dan `index.php`.
6. Jika ada SQL pembaruan, impor hanya file incremental yang sesuai.
7. Bersihkan cache seperti bagian A.11.
8. Uji login, upload, penerbitan, penarikan, dan unduhan.
9. Hapus folder sementara setelah situs dinyatakan normal.

Untuk pembaruan versi 19 Oktober 2026 pada situs yang sudah aktif, urutannya adalah: backup, upload paket baru, pertahankan `.env` dan `storage/app/private`, impor `database-import/update-2026-10-19.sql` satu kali melalui phpMyAdmin, lalu ganti isi `public_html`. Data mahasiswa, sertifikat lama, akun, dan dokumen yang sudah terbit tidak dihapus.

Untuk pembaruan perbaikan akun dan verifikasi tanggal 21 Oktober 2026, upload kode baru dengan tetap mempertahankan `.env` dan seluruh folder `storage/app/private`, lalu impor `database-import/update-2026-10-21.sql` satu kali. Ganti isi `public_html` dan bersihkan cache sesuai bagian A.11. Semua 77 nama tetap ditampilkan dalam pencarian. Menu **Data Mahasiswa** menampilkan daftar akademik, status pendaftaran, verifikasi email, dan akun lama yang belum tertaut. Nama yang sudah terhubung ke akun diberi keterangan **Sudah memiliki akun** dan tidak boleh didaftarkan dua kali.

### 3.2 Update dengan Git dan SSH

```bash
cd /var/www/skpi
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up
```

Cadangkan database dan `storage/app/private` sebelum perintah update. Jangan memakai `composer update` pada server produksi karena dapat memilih versi paket yang berbeda dari `composer.lock`.

## 4. Backup yang wajib dipertahankan

Cadangkan secara rutin:

- Database MySQL `u841207907_skpi`.
- `skpi_app/.env`.
- `skpi_app/storage/app/private/evidence`.
- `skpi_app/storage/app/private/skpi`.
- `skpi_app/resources/templates/skpi-template.docx`.

Uji pemulihan backup pada lokasi terpisah. Backup yang belum pernah diuji belum menjamin data dapat dipulihkan.

## 5. Troubleshooting

### Error 500

1. Periksa `skpi_app/storage/logs/laravel.log`.
2. Pastikan `.env` ada, `APP_KEY` terisi, dan `APP_DEBUG=false`.
3. Periksa nama database, user, password, dan host.
4. Pastikan `storage` dan `bootstrap/cache` dapat ditulis.
5. Pastikan PHP 8.2 atau lebih baru dan ekstensi `dom`, `xml`, `zip`, `mbstring`, `pdo_mysql`, serta `fileinfo` aktif.
6. Bersihkan cache lama.

### Halaman putih atau konfigurasi lama

Hapus cache di `bootstrap/cache` dan kompilasi tampilan di `storage/framework/views`. Periksa log setelah memuat ulang halaman.

### CSS, logo, atau JavaScript tidak muncul

1. Buka langsung `https://skpi.polteksi.ac.id/assets/app.css`.
2. Pastikan folder `public_html/assets` ada.
3. Pastikan `APP_URL` memakai HTTPS dan domain yang benar.
4. Bersihkan cache browser dan Cache Manager Hostinger.
5. Pastikan `public_html/index.php` memanggil `$app->usePublicPath(__DIR__)`.

### Semua URL selain halaman awal menghasilkan 404

Pastikan `public_html/.htaccess` ada dan modul rewrite aktif. Jangan menaruh `.htaccess` hanya di `skpi_app/public`.

### Gagal terhubung ke database

Periksa kredensial di hPanel dan `.env`. Nama database serta user Hostinger biasanya memiliki awalan akun dan harus ditulis lengkap.

### Email verifikasi tidak terkirim

1. Pastikan `.env` produksi lama tidak tertimpa dan `APP_KEY` tidak berubah.
2. Periksa SMTP, port, skema, alamat pengirim, dan password mailbox.
3. Bersihkan file cache konfigurasi di `bootstrap/cache` setelah mengubah `.env`.
4. Masuk sebagai admin dan gunakan tombol **Kirim email uji ke admin** pada Dashboard.
5. Lihat `storage/logs/laravel.log` jika pengiriman ditolak.
6. Pastikan alamat FROM sama dengan mailbox yang diizinkan penyedia SMTP.
7. Periksa SPF, DKIM, DMARC, kotak spam, dan reputasi domain jika SMTP menerima email tetapi inbox belum menerimanya.

Jika mahasiswa salah mengetik email, buka halaman verifikasi dan gunakan **Alamat email salah?** untuk mengganti alamat lalu mengirim tautan baru.

### Jumlah mahasiswa di admin berbeda dengan phpMyAdmin

Menu **Data Mahasiswa** membedakan empat angka: seluruh daftar akademik, daftar yang sudah mendaftar, yang belum mendaftar, dan seluruh akun mahasiswa. Perbedaan dapat terjadi karena akun demo atau akun lama tidak memiliki pasangan di daftar akademik. Impor `database-import/update-2026-10-21.sql` untuk menautkan pasangan yang NIM-nya sama tanpa menghapus data. Akun yang tetap tidak memiliki pasangan akan ditampilkan pada bagian **Akun belum tertaut ke daftar akademik**, bukan disembunyikan.

### Upload sertifikat gagal

1. Pastikan format PDF, JPG, JPEG, atau PNG.
2. Pastikan ukuran maksimal 5 MB.
3. Naikkan `upload_max_filesize` dan `post_max_size` di PHP Configuration jika nilainya lebih kecil dari kebutuhan.
4. Pastikan `storage/app/private/evidence` dapat ditulis.

### Word gagal dibuat

1. Pastikan ekstensi `zip`, `dom`, dan `xml` aktif.
2. Pastikan `resources/templates/skpi-template.docx` ada.
3. Isi `SKPI_SIGNATORY_NAME` dan `SKPI_SIGNATORY_NIDN`.
4. Pastikan `storage/app/private/skpi` dan `storage/app/private/tmp` dapat ditulis.

### SKPI ditarik masih bisa diunduh

Pastikan kode baru dan `database/update-2026-10-14.sql` sudah diterapkan. Status harus menjadi `withdrawn`, dan rute unduh hanya menerima status `issued`.

## 6. Checklist setelah deploy

- [ ] `https://skpi.polteksi.ac.id` terbuka melalui HTTPS.
- [ ] Tidak ada tautan `public_html` yang berstatus invalid.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` benar.
- [ ] Zona waktu aplikasi menunjukkan WIB.
- [ ] PHP 8.2 atau lebih baru aktif.
- [ ] Ekstensi PHP yang diperlukan aktif, terutama `dom`, `xml`, `zip`, `fileinfo`, dan `pdo_mysql`.
- [ ] Database `u841207907_skpi` dapat diakses.
- [ ] Tabel `skpi_withdrawals` tersedia.
- [ ] Login admin berhasil.
- [ ] Menu Data Mahasiswa menampilkan 77 data akademik dan status akun masing-masing.
- [ ] Akun mahasiswa lama yang belum tertaut tetap terlihat oleh admin.
- [ ] Admin dapat mencari, melihat, mengubah, menonaktifkan, mereset password, dan menghapus akun sesuai aturan relasi data.
- [ ] Registrasi mahasiswa dan pencarian nama berhasil pada desktop dan ponsel.
- [ ] Tombol tampilkan kata sandi berfungsi pada login, registrasi, dan reset kata sandi.
- [ ] Pesan validasi tampil dalam bahasa Indonesia.
- [ ] Upload PDF atau gambar maksimal 5 MB berhasil.
- [ ] Bukti sertifikat tersimpan di storage privat.
- [ ] Mahasiswa dapat mengajukan SKPI setelah satu sertifikat.
- [ ] Admin dapat menyetujui dan mengunduh Word.
- [ ] Admin dapat menarik SKPI dengan alasan.
- [ ] Dokumen yang ditarik tidak dapat diunduh.
- [ ] Mahasiswa menerima alasan penarikan dan dapat mengajukan lagi.
- [ ] Pengajuan baru mendapat nomor dokumen baru.
- [ ] ZIP Word massal admin berhasil.
- [ ] Tombol email uji admin berhasil dan email diterima.
- [ ] Email verifikasi terkirim melalui SMTP, termasuk saat tautan dibuka dari perangkat lain.
- [ ] Backup database, `.env`, template Word, bukti, dan dokumen sudah dibuat.

## Referensi resmi

- [Laravel 12 Deployment](https://laravel.com/docs/12.x/deployment)
- [Laravel 12 Task Scheduling](https://laravel.com/docs/12.x/scheduling)
- [Laravel 12 Queues](https://laravel.com/docs/12.x/queues)
- [Hostinger: Deploy Laravel](https://www.hostinger.com/tutorials/how-to-deploy-laravel/)
- [Hostinger: PHP extensions and options](https://www.hostinger.com/support/4667515-how-to-manage-php-extensions-and-options-in-hostinger/)
- [Hostinger: Laravel VPS template](https://www.hostinger.com/support/9070980-how-to-use-the-laravel-vps-template-at-hostinger/)
