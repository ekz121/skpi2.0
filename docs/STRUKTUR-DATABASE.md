# Struktur Database SKEM dan SKPI

Database produksi: `u841207907_skpi`.

## Tabel aplikasi

| Tabel | Isi utama |
| --- | --- |
| `users` | Akun mahasiswa, verifikator prodi, admin, dan pimpinan; mapping unit serta status aktif |
| `faculties` | Master fakultas atau unit induk untuk pemetaan user dan program studi |
| `student_profiles` | NIM, program studi, data lahir, tahun lulus, nomor ijazah, dan gelar |
| `student_registries` | 77 data akademik yang menjadi sumber registrasi mahasiswa |
| `study_programs` | Program studi, fakultas, gelar, akreditasi, dan capaian pembelajaran |
| `activity_rules` | Kategori, jenis kegiatan, bukti, dan bobot poin |
| `submissions` | Data sertifikat, jenis kegiatan bebas (`custom_activity_type`), lampiran privat, poin, dan pemeriksaan admin |
| `submission_decisions` | Riwayat keputusan sertifikat versi alur lama |
| `skpi_requests` | Pengajuan SKPI, status, nomor, snapshot, dan lokasi Word |
| `skpi_withdrawals` | Riwayat penarikan SKPI, admin, waktu, alasan, nomor, dan lokasi dokumen lama |
| `app_notifications` | Notifikasi dalam aplikasi untuk setiap akun |
| `sessions` | Sesi login berbasis database |
| `cache`, `cache_locks` | Cache Laravel |
| `jobs`, `job_batches`, `failed_jobs` | Infrastruktur queue jika kelak memakai queue database |
| `password_reset_tokens` | Token reset kata sandi |
| `migrations` | Riwayat migrasi skema |

## Status SKPI

| Status | Arti |
| --- | --- |
| `pending` | Menunggu keputusan admin |
| `issued` | Word sah dan dapat diunduh |
| `rejected` | Pengajuan ditolak dan dapat diajukan ulang |
| `failed` | Pembuatan Word gagal dan dapat dicoba kembali admin |
| `withdrawn` | Nomor lama tidak berlaku, unduhan terkunci, dan mahasiswa dapat mengajukan baru |

Penarikan tidak menghapus `skpi_requests`, file Word, sertifikat, atau poin. Catatan audit tersimpan di `skpi_withdrawals`.

## File SQL

- `database/skpi-hostinger.sql`: instalasi baru. Berisi struktur dan data awal. File ini menghapus tabel sebelum membuat ulang.
- `database/update-2026-10-14.sql`: pembaruan situs yang sudah aktif. Menambah status dan tabel penarikan tanpa menghapus data pengguna.
- `database/update-2026-10-15.sql`: pembaruan situs aktif untuk empat role, fakultas/prodi, status akun, dan audit penonaktifan.

## Kebijakan akun

- `users.role` menerima `student`, `program_verifier`, `admin`, dan `leader`.
- `users.is_active = 0` menghentikan akses login tanpa menghapus data.
- User yang sudah mempunyai capaian, riwayat verifikasi, atau SKPI tidak boleh dihapus permanen.
- Email pada `users` dan NIM pada `student_profiles` bersifat unik.

Selalu export database sebelum impor SQL.
