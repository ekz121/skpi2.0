# Struktur database

Struktur MySQL dibuat oleh migration Laravel. Data program studi, 77 mahasiswa sumber, akun admin, aturan poin, dan sembilan akun uji dimasukkan oleh seeder. Karena itu, saat hosting tidak perlu membuat tabel atau mengimpor SQL secara manual.

## Tabel aplikasi

| Tabel | Isi utama |
| --- | --- |
| `users` | Akun, email, password terenkripsi, peran admin atau mahasiswa |
| `study_programs` | Kode internal, kode nasional, nama prodi, gelar, akreditasi, capaian pembelajaran |
| `student_registries` | 77 data sumber: NIM, nama, prodi, angkatan 2023, tahun lulus 2026, nomor ijazah, dan tautan akun yang telah mendaftar |
| `student_profiles` | Biodata akun mahasiswa untuk dokumen SKPI |
| `activity_rules` | Kategori, jenis kegiatan, level, prestasi, poin, dan bukti wajib |
| `submissions` | Sertifikat mahasiswa, bukti privat, poin, status otomatis diterima, dan status pemeriksaan admin |
| `submission_decisions` | Riwayat keputusan sertifikat dari alur versi lama, dipertahankan untuk kompatibilitas data |
| `skpi_requests` | Permintaan, keputusan admin, snapshot data, nomor dokumen, dan lokasi Word DOCX |
| `app_notifications` | Notifikasi di dalam aplikasi |

Laravel juga membuat tabel teknis `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, dan `failed_jobs`.

## Data mahasiswa sumber

Sumber yang sudah dipindahkan ke aplikasi berada di `database/data/student-registry.php`.

| Kode | Program studi | Jumlah | Gelar otomatis |
| --- | --- | ---: | --- |
| 21401 | Teknologi Mesin | 33 | A.Md.T. |
| 61401 | Administrasi Perkantoran | 14 | A.Md.A.B. |
| 62401 | Akuntansi | 14 | A.Md.Ak. |
| 57403 | Teknologi Informasi | 16 | A.Md.Kom. |
| | Total | 77 | |

Data nama, NIM, dan nomor ijazah disalin apa adanya dari empat spreadsheet sumber. Registrasi hanya menampilkan data yang belum ditautkan ke akun, sehingga satu identitas tidak dapat didaftarkan dua kali.

## Membuat dan mengisi tabel

Pada instalasi baru jalankan:

```bash
php artisan migrate --seed --force
```

Jangan gunakan `migrate:fresh` di situs yang sudah memiliki data karena perintah tersebut menghapus seluruh tabel sebelum membuatnya kembali.

Verifikasi data sesudah instalasi:

```bash
php artisan tinker --execute="echo App\\Models\\StudentRegistry::count();"
php artisan tinker --execute="echo App\\Models\\User::where('email', 'like', 'demo%@demo.polteksi.ac.id')->count();"
```

Hasil yang diharapkan berturut-turut adalah `77` dan `9`. Password tersimpan dalam bentuk hash. Jangan memasukkan atau mengganti password langsung melalui phpMyAdmin.
