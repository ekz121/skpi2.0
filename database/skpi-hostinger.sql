-- Database awal SKPI untuk phpMyAdmin
-- Domain: https://skpi.polteksi.ac.id/
-- Impor ke database u841207907_skpi

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_rules`;
CREATE TABLE `activity_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `subcategory` varchar(255) NOT NULL,
  `activity_type` varchar(255) NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `achievement` varchar(255) DEFAULT NULL,
  `duration_label` varchar(255) DEFAULT NULL,
  `points` smallint(5) unsigned NOT NULL,
  `evidence_label` varchar(255) NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_rules_category_subcategory_index` (`category`,`subcategory`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_rules` (`id`, `category`, `subcategory`, `activity_type`, `level`, `achievement`, `duration_label`, `points`, `evidence_label`, `is_mandatory`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Nasional', 'Juara 1', NULL, '200', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Nasional', 'Juara 2', NULL, '180', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('3', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Nasional', 'Juara 3', NULL, '150', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('4', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Nasional', 'Juara Harapan', NULL, '130', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('5', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Nasional', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('6', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Provinsi/Regional', 'Juara 1', NULL, '150', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('7', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Provinsi/Regional', 'Juara 2', NULL, '130', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('8', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Provinsi/Regional', 'Juara 3', NULL, '100', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('9', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Provinsi/Regional', 'Juara Harapan', NULL, '80', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('10', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Provinsi/Regional', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('11', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Kota/Kabupaten', 'Juara 1', NULL, '80', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('12', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Kota/Kabupaten', 'Juara 2', NULL, '60', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('13', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Kota/Kabupaten', 'Juara 3', NULL, '50', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('14', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Kota/Kabupaten', 'Juara Harapan', NULL, '40', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('15', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'Kota/Kabupaten', 'Keikutsertaan', NULL, '20', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('16', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'DIKTI', 'Juara 1', NULL, '150', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('17', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'DIKTI', 'Juara 2', NULL, '130', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('18', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'DIKTI', 'Juara 3', NULL, '100', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('19', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'DIKTI', 'Juara Harapan', NULL, '80', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('20', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba akademik', 'Lomba', 'DIKTI', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('21', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Nasional', 'Juara 1', NULL, '200', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('22', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Nasional', 'Juara 2', NULL, '180', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('23', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Nasional', 'Juara 3', NULL, '150', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('24', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Nasional', 'Juara Harapan', NULL, '130', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('25', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Nasional', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('26', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Provinsi/Regional', 'Juara 1', NULL, '150', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('27', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Provinsi/Regional', 'Juara 2', NULL, '130', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('28', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Provinsi/Regional', 'Juara 3', NULL, '100', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('29', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Provinsi/Regional', 'Juara Harapan', NULL, '80', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('30', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Provinsi/Regional', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('31', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Kota/Kabupaten', 'Juara 1', NULL, '80', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('32', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Kota/Kabupaten', 'Juara 2', NULL, '60', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('33', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Kota/Kabupaten', 'Juara 3', NULL, '50', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('34', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Kota/Kabupaten', 'Juara Harapan', NULL, '40', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('35', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'Kota/Kabupaten', 'Keikutsertaan', NULL, '20', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('36', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'DIKTI', 'Juara 1', NULL, '150', 'Sertifikat Juara 1', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('37', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'DIKTI', 'Juara 2', NULL, '130', 'Sertifikat Juara 2', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('38', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'DIKTI', 'Juara 3', NULL, '100', 'Sertifikat Juara 3', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('39', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'DIKTI', 'Juara Harapan', NULL, '80', 'Sertifikat Juara Harapan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('40', 'Prestasi Akademik dan Nonakademik', 'Prestasi lomba nonakademik', 'Lomba', 'DIKTI', 'Keikutsertaan', NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('41', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Sertifikasi/pelatihan Disnaker', NULL, NULL, NULL, '150', 'Sertifikat Kompetensi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('42', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Sertifikasi/pelatihan BNSP', NULL, NULL, NULL, '100', 'Sertifikat Kompetensi', '1', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('43', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Sertifikat Kompetensi dari LSP dalam dan luar negeri', NULL, NULL, NULL, '150', 'Sertifikat Kompetensi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('44', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Magang/PKL 3-6 bulan (MSIB/Magenta)', NULL, NULL, NULL, '80', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('45', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Magang/PKL 3-6 bulan (reguler)', NULL, NULL, NULL, '60', 'Sertifikat Keikutsertaan', '1', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('46', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'PKKMB', NULL, NULL, NULL, '40', 'Sertifikat Keikutsertaan', '1', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('47', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Pelatihan 3 hari atau lebih', NULL, NULL, NULL, '80', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('48', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Pelantikan LKMM Pra TD/TD', NULL, NULL, NULL, '40', 'Sertifikat Keikutsertaan', '1', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('49', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Seminar nasional sehari', NULL, NULL, NULL, '30', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('50', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Pelatihan/seminar sehari dari perusahaan', NULL, NULL, NULL, '20', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('51', 'Pelatihan dan Keprofesian', 'Pelatihan, sertifikasi, magang, dan seminar', 'Seminar online/webinar sehari', NULL, NULL, NULL, '10', 'Sertifikat Keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('52', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Presiden BEM', NULL, NULL, NULL, '100', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('53', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Wakil Presiden BEM', NULL, NULL, NULL, '100', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('54', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua DPM', NULL, NULL, NULL, '100', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('55', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua organisasi luar kampus tingkat nasional', NULL, NULL, NULL, '100', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('56', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Wakil ketua luar kampus tingkat nasional', NULL, NULL, NULL, '100', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('57', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua HIMA', NULL, NULL, NULL, '80', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('58', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Wakil Ketua HIMA', NULL, NULL, NULL, '80', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('59', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua organisasi luar kampus tingkat regional/provinsi', NULL, NULL, NULL, '80', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('60', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Wakil ketua luar kampus tingkat regional/provinsi', NULL, NULL, NULL, '80', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('61', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Koordinator/Menteri/Sekretaris/Bendahara BEM', NULL, NULL, NULL, '70', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('62', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota BEM', NULL, NULL, NULL, '50', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('63', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota organisasi luar kampus tingkat nasional', NULL, NULL, NULL, '50', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('64', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota HIMA', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('65', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua dan wakil kegiatan', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('66', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Ketua organisasi luar kampus tingkat kabupaten/kota', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('67', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Wakil ketua luar kampus tingkat kabupaten/kota', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('68', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota organisasi luar kampus tingkat regional/provinsi', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('69', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Koordinator kegiatan', NULL, NULL, NULL, '40', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('70', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota organisasi luar kampus tingkat kabupaten/kota', NULL, NULL, NULL, '30', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('71', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Keikutsertaan ekstrakurikuler minimal 1 semester dan hadir minimal 10 kali', NULL, NULL, NULL, '50', 'Sertifikat Keikutsertaan Ekstrakurikuler', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('72', 'Organisasi dan Kepemimpinan', 'Jabatan, kepanitiaan, dan ekstrakurikuler', 'Anggota/volunteer kegiatan', NULL, NULL, NULL, '30', 'Sertifikat atau SK resmi', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('73', 'Proyek, Penelitian, dan Pengabdian Masyarakat', 'Proyek, penelitian, dan pengabdian', 'Keterlibatan lebih dari 3 bulan', NULL, NULL, '> 3 bulan', '50', 'Bukti jurnal/proyek keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('74', 'Proyek, Penelitian, dan Pengabdian Masyarakat', 'Proyek, penelitian, dan pengabdian', 'Keterlibatan 2 minggu sampai 1 bulan', NULL, NULL, '2 minggu - 1 bulan', '30', 'Bukti jurnal/proyek keikutsertaan', '0', '1', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('75', 'Sertifikat Magang', 'Magang', 'Magang Industri', NULL, NULL, NULL, '60', 'Sertifikat magang', '0', '1', '2026-10-19 00:00:00', '2026-10-19 00:00:00'),
('76', 'Sertifikat Lain-lain', 'Lain-lain', 'Lain-lain', NULL, NULL, NULL, '0', 'Sertifikat atau dokumen resmi', '0', '1', '2026-10-19 00:00:00', '2026-10-19 00:00:00');

DROP TABLE IF EXISTS `app_notifications`;
CREATE TABLE `app_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_10_05_000100_create_skem_skpi_tables', '1'),
('5', '2026_10_07_000200_add_word_documents_and_rejection_status', '1'),
('6', '2026_10_07_000300_update_default_admin_credentials', '1'),
('7', '2026_10_08_000400_add_admin_review_to_submissions', '1'),
('8', '2026_10_09_000500_add_student_registry_and_word_only_delivery', '1'),
('9', '2026_10_14_000600_add_skpi_withdrawal_history', '1'),
('10', '2026_10_15_000700_add_user_management', '1'),
('11', '2026_10_19_000800_add_special_certificate_types', '1'),
('12', '2026_10_21_000900_reconcile_student_accounts', '1');

DROP TABLE IF EXISTS `faculties`;
CREATE TABLE `faculties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculties_code_unique` (`code`),
  UNIQUE KEY `faculties_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faculties` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
('1', 'POLTEKSI', 'Politeknik Semen Indonesia', '2026-10-15 00:00:00', '2026-10-15 00:00:00');

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `skpi_requests`;
CREATE TABLE `skpi_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','revision','issued','failed','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `docx_path` varchar(255) DEFAULT NULL,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot`)),
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skpi_requests_document_number_unique` (`document_number`),
  KEY `skpi_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `skpi_requests_user_id_status_index` (`user_id`,`status`),
  KEY `skpi_requests_status_index` (`status`),
  CONSTRAINT `skpi_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `skpi_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `skpi_withdrawals`;
CREATE TABLE `skpi_withdrawals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `skpi_request_id` bigint(20) unsigned NOT NULL,
  `withdrawn_by` bigint(20) unsigned DEFAULT NULL,
  `reason` text NOT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `withdrawn_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `skpi_withdrawals_skpi_request_id_foreign` (`skpi_request_id`),
  KEY `skpi_withdrawals_withdrawn_by_foreign` (`withdrawn_by`),
  CONSTRAINT `skpi_withdrawals_skpi_request_id_foreign` FOREIGN KEY (`skpi_request_id`) REFERENCES `skpi_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `skpi_withdrawals_withdrawn_by_foreign` FOREIGN KEY (`withdrawn_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `student_profiles`;
CREATE TABLE `student_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `study_program_id` bigint(20) unsigned NOT NULL,
  `nim` varchar(30) NOT NULL,
  `cohort` year(4) NOT NULL,
  `birthplace` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `diploma_number` varchar(255) DEFAULT NULL,
  `academic_title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `student_profiles_nim_unique` (`nim`),
  KEY `student_profiles_study_program_id_foreign` (`study_program_id`),
  CONSTRAINT `student_profiles_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`),
  CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `student_profiles` (`id`, `user_id`, `study_program_id`, `nim`, `cohort`, `birthplace`, `birthdate`, `graduation_year`, `diploma_number`, `academic_title`, `created_at`, `updated_at`) VALUES
('1', '2', '1', 'DUMMY-2023-001', '2023', 'Kota Uji', '2004-01-01', '2026', 'DUMMY-IJAZAH-001', 'A.Md.Kom.', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', '3', '2', 'DUMMY-2023-002', '2023', 'Kota Uji', '2004-02-01', '2026', 'DUMMY-IJAZAH-002', 'A.Md.T.', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('3', '4', '3', 'DUMMY-2023-003', '2023', 'Kota Uji', '2004-03-01', '2026', 'DUMMY-IJAZAH-003', 'A.Md.A.B.', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('4', '5', '4', 'DUMMY-2023-004', '2023', 'Kota Uji', '2004-04-01', '2026', 'DUMMY-IJAZAH-004', 'A.Md.Ak.', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('5', '6', '1', 'DUMMY-2023-005', '2023', 'Kota Uji', '2004-05-01', '2026', 'DUMMY-IJAZAH-005', 'A.Md.Kom.', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('6', '7', '2', 'DUMMY-2023-006', '2023', 'Kota Uji', '2004-06-01', '2026', 'DUMMY-IJAZAH-006', 'A.Md.T.', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('7', '8', '3', 'DUMMY-2023-007', '2023', 'Kota Uji', '2004-07-01', '2026', 'DUMMY-IJAZAH-007', 'A.Md.A.B.', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('8', '9', '4', 'DUMMY-2023-008', '2023', 'Kota Uji', '2004-08-01', '2026', 'DUMMY-IJAZAH-008', 'A.Md.Ak.', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('9', '10', '1', 'DUMMY-2023-009', '2023', 'Kota Uji', '2004-09-01', '2026', 'DUMMY-IJAZAH-009', 'A.Md.Kom.', '2026-10-12 19:22:51', '2026-10-12 19:22:51');

DROP TABLE IF EXISTS `student_registries`;
CREATE TABLE `student_registries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `study_program_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `nim` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `cohort` year(4) NOT NULL DEFAULT 2023,
  `graduation_year` year(4) NOT NULL DEFAULT 2026,
  `diploma_number` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_registries_nim_unique` (`nim`),
  UNIQUE KEY `student_registries_diploma_number_unique` (`diploma_number`),
  UNIQUE KEY `student_registries_user_id_unique` (`user_id`),
  KEY `student_registries_study_program_id_foreign` (`study_program_id`),
  KEY `student_registries_name_index` (`name`),
  CONSTRAINT `student_registries_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`),
  CONSTRAINT `student_registries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `student_registries` (`id`, `study_program_id`, `user_id`, `nim`, `name`, `cohort`, `graduation_year`, `diploma_number`, `created_at`, `updated_at`) VALUES
('1', '2', NULL, '23011003', 'M. ROBBACH MAHILAL', '2023', '2026', '075021214012026100001', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', '2', NULL, '23011015', 'MUHAMMDA DANDA QODIBAS HAQ', '2023', '2026', '075021214012026100002', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('3', '2', NULL, '23011028', 'DANENDRA RADITHIYA BAGUS PUTRA', '2023', '2026', '075021214012026100003', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('4', '2', NULL, '23012018', 'ROLLEN DIAN SAPUTRA', '2023', '2026', '075021214012026100004', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('5', '2', NULL, '23012021', 'MOHAMMAD FERDIAN ABDI NUGROHO', '2023', '2026', '075021214012026100005', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('6', '2', NULL, '23012026', 'ADRIAN MAULANA SADEWA', '2023', '2026', '075021214012026100006', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('7', '2', NULL, '23012029', 'MUHAMMAD FATHAN KURNIANSYAH', '2023', '2026', '075021214012026100007', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('8', '2', NULL, '23012030', 'MOHAMMAD AKMAL FIRMANSYAH', '2023', '2026', '075021214012026100008', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('9', '2', NULL, '23012031', 'ANDIKA RIFQI MAULANA', '2023', '2026', '075021214012026100009', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('10', '2', NULL, '23012032', 'DENDY UWAIS AL QORONI', '2023', '2026', '075021214012026100010', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('11', '2', NULL, '23013008', 'AHMAD MUZAKI AL FANANI', '2023', '2026', '075021214012026100011', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('12', '2', NULL, '23013035', 'PUTRA DWI MAHADANA', '2023', '2026', '075021214012026100012', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('13', '2', NULL, '23013036', 'FARHAN MINAN NUR ROHMAN', '2023', '2026', '075021214012026100013', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('14', '2', NULL, '23014004', 'DAMAR RADHITYATAMA', '2023', '2026', '075021214012026100014', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('15', '2', NULL, '23014039', 'MUHAMMAD ARIQ PRASETYA', '2023', '2026', '075021214012026100015', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('16', '2', NULL, '23015001', 'AHMAD ARIF FIRMANSYAH', '2023', '2026', '075021214012026100016', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('17', '2', NULL, '23015005', 'KEVIN WAHYU ARSANTA', '2023', '2026', '075021214012026100017', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('18', '2', NULL, '23015006', 'ALIF JAUHAR FIKRI', '2023', '2026', '075021214012026100018', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('19', '2', NULL, '23015007', 'MUHIBBUL FIRDAUS', '2023', '2026', '075021214012026100019', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('20', '2', NULL, '23015011', 'DIMAS PUTRA PRATAMA', '2023', '2026', '075021214012026100020', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('21', '2', NULL, '23015013', 'SYARIF BAGUS WIRA ALGHANI', '2023', '2026', '075021214012026100021', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('22', '2', NULL, '23015014', 'SOFIA HEDI PUTRI', '2023', '2026', '075021214012026100022', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('23', '2', NULL, '23015016', 'MOHAMMAD MASHADI', '2023', '2026', '075021214012026100023', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('24', '2', NULL, '23015017', 'MOCHAMAD ARIF SANTOSO', '2023', '2026', '075021214012026100024', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('25', '2', NULL, '23016002', 'MUHAMMAD FAIZUN ABDHILLAH', '2023', '2026', '075021214012026100025', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('26', '2', NULL, '23016009', 'ROMY MARYONO PUTRA', '2023', '2026', '075021214012026100026', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('27', '2', NULL, '23016010', 'BILAL AL BAHIJ', '2023', '2026', '075021214012026100027', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('28', '2', NULL, '23016019', 'JEREMY OWSRAN VERDIAN SOEWARDHI', '2023', '2026', '075021214012026100028', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('29', '2', NULL, '23016023', 'ARJUNA TEGUH SABDA PUTRA', '2023', '2026', '075021214012026100029', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('30', '2', NULL, '23016024', 'AAN MAHMUD', '2023', '2026', '075021214012026100030', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('31', '2', NULL, '23016027', 'M RIZKI TRI BAYUDI', '2023', '2026', '075021214012026100031', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('32', '2', NULL, '23016037', 'MUHAMMAD NAUFAL MAHDI SANY', '2023', '2026', '075021214012026100032', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('33', '2', NULL, '23016042', 'Muhammad ALI MASKUR', '2023', '2026', '075021214012026100033', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('34', '3', NULL, '23043001', 'ROUNAQ INDRA ROBIKHA', '2023', '2026', '075021614012026100001', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('35', '3', NULL, '23043006', 'SAVINA PUTRI SUNHADI', '2023', '2026', '075021614012026100002', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('36', '3', NULL, '23043016', 'DIANA TRI PUSPITA ANGGRAINI', '2023', '2026', '075021614012026100003', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('37', '3', NULL, '23045004', 'YAZKA RIFQIA HERLANTIN', '2023', '2026', '075021614012026100004', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('38', '3', NULL, '23046002', 'SULAIHA DIAN RIZQI', '2023', '2026', '075021614012026100005', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('39', '3', NULL, '23046005', 'MAULANA FIRDAUS AL FURQON', '2023', '2026', '075021614012026100006', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('40', '3', NULL, '23046007', 'DEWA AYU TITAH GAYATRI', '2023', '2026', '075021614012026100007', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('41', '3', NULL, '23046010', 'A. TAUFIQ ISNAINI ARDIAN SYAH', '2023', '2026', '075021614012026100008', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('42', '3', NULL, '23046011', 'MUHAMMAD RIFQI ZAIN', '2023', '2026', '075021614012026100009', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('43', '3', NULL, '23046012', 'SILVI INDRI NAFURI', '2023', '2026', '075021614012026100010', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('44', '3', NULL, '23046013', 'NURIKO PUJI FEBRIANTI', '2023', '2026', '075021614012026100011', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('45', '3', NULL, '23046014', 'HALIMAH IZZATI', '2023', '2026', '075021614012026100012', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('46', '3', NULL, '23046018', 'MUHAMMAD IQBALUSTSANI', '2023', '2026', '075021614012026100013', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('47', '3', NULL, '23046020', 'ELISABETH VERONICA KURNIA RAHMADHONI', '2023', '2026', '075021614012026100014', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('48', '4', NULL, '23032012', 'ACHMAD RIZKY PURNOMO', '2023', '2026', '075021624012026100001', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('49', '4', NULL, '23033006', 'MOHAMMAD IFANDY ERIADI', '2023', '2026', '075021624012026100002', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('50', '4', NULL, '23033014', 'FAHRIEZA ARYA PRATAMA', '2023', '2026', '075021624012026100003', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('51', '4', NULL, '23034001', 'NASYWA ADELIA JAUHARAH SALSABILA', '2023', '2026', '075021624012026100004', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('52', '4', NULL, '23034021', 'IZKIYAH HAFFATIL WARDATI', '2023', '2026', '075021624012026100005', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('53', '4', NULL, '23035003', 'AINUR FAIZAH', '2023', '2026', '075021624012026100006', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('54', '4', NULL, '23036004', 'NAILA DWI SEPTYA', '2023', '2026', '075021624012026100007', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('55', '4', NULL, '23036009', 'RIZKIA FAHRINA', '2023', '2026', '075021624012026100008', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('56', '4', NULL, '23036011', 'ARYN FYNKA SUSILO', '2023', '2026', '075021624012026100009', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('57', '4', NULL, '23036016', 'MUHAMMAD DIMAS MAULANA IBRAHIM', '2023', '2026', '075021624012026100010', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('58', '4', NULL, '23036017', 'ARIFATUL HASANAH', '2023', '2026', '075021624012026100011', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('59', '4', NULL, '23036018', 'WINARTIN NASICHAH', '2023', '2026', '075021624012026100012', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('60', '4', NULL, '23036019', 'SYURURI MAUDHUNAH', '2023', '2026', '075021624012026100013', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('61', '4', NULL, '23036020', 'FADHILATUL MUKMINAH', '2023', '2026', '075021624012026100014', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('62', '1', NULL, '23022007', 'AGIAN SATRIO PRAMONO', '2023', '2026', '075021574032026100001', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('63', '1', NULL, '23022008', 'AHMAD HAFIDZ SALMAN FARIZ', '2023', '2026', '075021574032026100002', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('64', '1', NULL, '23023001', 'AHMAD SYARIFUDIN HIDAYATULLAH', '2023', '2026', '075021574032026100003', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('65', '1', NULL, '23023020', 'MOCHAMMAD \'AZMIL UMUR', '2023', '2026', '075021574032026100004', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('66', '1', NULL, '23023022', 'ASRIL', '2023', '2026', '075021574032026100005', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('67', '1', NULL, '23025006', 'AHMAD RIFQI AL FARUQ', '2023', '2026', '075021574032026100006', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('68', '1', NULL, '23026002', 'FERDI PRATAMA OKTAVIANTO', '2023', '2026', '075021574032026100007', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('69', '1', NULL, '23026004', 'ABUDZAR MOHAMMAD AL FARIZI', '2023', '2026', '075021574032026100008', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('70', '1', NULL, '23026005', 'ALIF FERDIANSYAH', '2023', '2026', '075021574032026100009', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('71', '1', NULL, '23026010', 'IVAN ROY PANDHE', '2023', '2026', '075021574032026100010', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('72', '1', NULL, '23026013', 'NOVANDRYA RAMADHAN', '2023', '2026', '075021574032026100011', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('73', '1', NULL, '23026014', 'MOCH. SANDI EKY MAHENDRA', '2023', '2026', '075021574032026100012', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('74', '1', NULL, '23026016', 'GRESLANANTA WAHYU ARDHANA', '2023', '2026', '075021574032026100013', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('75', '1', NULL, '23026017', 'AHLUL FIRDAUS', '2023', '2026', '075021574032026100014', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('76', '1', NULL, '23026018', 'FATIH LUTHFI', '2023', '2026', '075021574032026100015', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('77', '1', NULL, '23026019', 'MUHAMMAD BRILLIAN MUJAHID KAMAL', '2023', '2026', '075021574032026100016', '2026-10-12 19:22:49', '2026-10-12 19:22:49');

DROP TABLE IF EXISTS `study_programs`;
CREATE TABLE `study_programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `national_code` varchar(10) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `academic_title` varchar(30) DEFAULT NULL,
  `accreditation` varchar(255) DEFAULT NULL,
  `learning_outcomes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`learning_outcomes`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `study_programs_code_unique` (`code`),
  UNIQUE KEY `study_programs_name_unique` (`name`),
  UNIQUE KEY `study_programs_national_code_unique` (`national_code`),
  KEY `study_programs_faculty_id_foreign` (`faculty_id`),
  CONSTRAINT `study_programs_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `study_programs` (`id`, `code`, `national_code`, `name`, `academic_title`, `accreditation`, `learning_outcomes`, `created_at`, `updated_at`) VALUES
('1', 'TI', '57403', 'Teknologi Informasi', 'A.Md.Kom.', NULL, '{\"Sikap dan Tata Nilai\":[\"Bertakwa kepada Tuhan Yang Maha Esa dan menunjukkan sikap religius.\",\"Menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan agama, moral, dan etika.\",\"Menginternalisasi nilai, norma, dan etika akademik.\"],\"Kemampuan Kerja Umum\":[\"Mampu menyelesaikan pekerjaan berlingkup luas dan memilih metode yang sesuai dengan menganalisis data.\",\"Mampu menunjukkan kinerja bermutu dan terukur.\",\"Mampu memecahkan masalah pekerjaan sesuai bidang keahlian terapannya.\"],\"Kemampuan Kerja Khusus\":[\"Mampu merancang, membangun, menguji, dan mengelola solusi berbasis perangkat lunak dan jaringan komputer secara bertanggung jawab.\",\"Mampu menerapkan algoritma, basis data, rekayasa perangkat lunak, dan infrastruktur TI untuk menyelesaikan masalah.\"],\"Penguasaan Pengetahuan\":[\"Menguasai konsep teoritis Teknologi Informasi, meliputi algoritma, struktur data, basis data, dan jaringan komputer.\",\"Menguasai prinsip metode penyelesaian masalah teknis dalam pengembangan perangkat lunak.\"]}', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', 'TM', '21401', 'Teknologi Mesin', 'A.Md.T.', NULL, '[]', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('3', 'AP', '61401', 'Administrasi Perkantoran', 'A.Md.A.B.', NULL, '[]', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('4', 'AK', '62401', 'Akuntansi', 'A.Md.Ak.', NULL, '[]', '2026-10-12 19:22:49', '2026-10-12 19:22:49');

UPDATE `study_programs` SET `faculty_id` = 1;

DROP TABLE IF EXISTS `submission_decisions`;
CREATE TABLE `submission_decisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) unsigned NOT NULL,
  `admin_id` bigint(20) unsigned NOT NULL,
  `decision` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `points` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submission_decisions_submission_id_foreign` (`submission_id`),
  KEY `submission_decisions_admin_id_foreign` (`admin_id`),
  CONSTRAINT `submission_decisions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  CONSTRAINT `submission_decisions_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `submissions`;
CREATE TABLE `submissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `activity_rule_id` bigint(20) unsigned NOT NULL,
  `custom_activity_type` varchar(180) DEFAULT NULL,
  `activity_name` varchar(255) NOT NULL,
  `organizer` varchar(255) NOT NULL,
  `started_at` date NOT NULL,
  `ended_at` date DEFAULT NULL,
  `certificate_number` varchar(255) DEFAULT NULL,
  `verification_url` varchar(255) DEFAULT NULL,
  `evidence_path` varchar(255) DEFAULT NULL,
  `evidence_original_name` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','revision','approved','rejected') NOT NULL DEFAULT 'draft',
  `estimated_points` smallint(5) unsigned NOT NULL,
  `approved_points` smallint(5) unsigned DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `admin_checked_by` bigint(20) unsigned DEFAULT NULL,
  `admin_checked_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submissions_activity_rule_id_foreign` (`activity_rule_id`),
  KEY `submissions_verified_by_foreign` (`verified_by`),
  KEY `submissions_user_id_status_index` (`user_id`,`status`),
  KEY `submissions_status_index` (`status`),
  KEY `submissions_admin_checked_by_foreign` (`admin_checked_by`),
  KEY `submissions_admin_checked_at_index` (`admin_checked_at`),
  CONSTRAINT `submissions_activity_rule_id_foreign` FOREIGN KEY (`activity_rule_id`) REFERENCES `activity_rules` (`id`),
  CONSTRAINT `submissions_admin_checked_by_foreign` FOREIGN KEY (`admin_checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `submissions` (`id`, `user_id`, `activity_rule_id`, `activity_name`, `organizer`, `started_at`, `ended_at`, `certificate_number`, `verification_url`, `evidence_path`, `evidence_original_name`, `status`, `estimated_points`, `approved_points`, `admin_note`, `verified_by`, `verified_at`, `admin_checked_by`, `admin_checked_at`, `submitted_at`, `created_at`, `updated_at`) VALUES
('1', '2', '51', 'Sertifikat Simulasi 1', 'Penyelenggara Data Uji', '2025-01-01', NULL, 'DUMMY-CERT-001', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:49', NULL, NULL, '2026-10-12 19:22:49', '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', '3', '51', 'Sertifikat Simulasi 2', 'Penyelenggara Data Uji', '2025-01-02', NULL, 'DUMMY-CERT-002', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:50', NULL, NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('3', '4', '51', 'Sertifikat Simulasi 3', 'Penyelenggara Data Uji', '2025-01-03', NULL, 'DUMMY-CERT-003', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:50', NULL, NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('4', '5', '51', 'Sertifikat Simulasi 4', 'Penyelenggara Data Uji', '2025-01-04', NULL, 'DUMMY-CERT-004', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:50', NULL, NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('5', '6', '51', 'Sertifikat Simulasi 5', 'Penyelenggara Data Uji', '2025-01-05', NULL, 'DUMMY-CERT-005', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:50', NULL, NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('6', '7', '51', 'Sertifikat Simulasi 6', 'Penyelenggara Data Uji', '2025-01-06', NULL, 'DUMMY-CERT-006', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:51', NULL, NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('7', '8', '51', 'Sertifikat Simulasi 7', 'Penyelenggara Data Uji', '2025-01-07', NULL, 'DUMMY-CERT-007', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:51', NULL, NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('8', '9', '51', 'Sertifikat Simulasi 8', 'Penyelenggara Data Uji', '2025-01-08', NULL, 'DUMMY-CERT-008', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:51', NULL, NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('9', '10', '51', 'Sertifikat Simulasi 9', 'Penyelenggara Data Uji', '2025-01-09', NULL, 'DUMMY-CERT-009', NULL, NULL, NULL, 'approved', '10', '10', NULL, NULL, '2026-10-12 19:22:51', NULL, NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51', '2026-10-12 19:22:51');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('student','program_verifier','admin','leader') NOT NULL DEFAULT 'student',
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `study_program_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `deactivated_by` bigint(20) unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_faculty_id_foreign` (`faculty_id`),
  KEY `users_study_program_id_foreign` (`study_program_id`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_deactivated_by_foreign` (`deactivated_by`),
  CONSTRAINT `users_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_deactivated_by_foreign` FOREIGN KEY (`deactivated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
('1', 'Admin SKPI', 'admin@polteksi.ac.id', 'admin', '2026-10-12 19:22:49', '$2y$12$3AXew9T./t6qVHEhX.K6G.0DQqi9qnFNQ8h8srYyp/o8u1Fjnqui2', NULL, '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('2', 'Mahasiswa Uji Alfa', 'demo01@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:49', '$2y$12$FiQKFb/viYdj8qp72euIrO7I9uO4S5LVWXa/JlLTbM.ReNKwxzekS', NULL, '2026-10-12 19:22:49', '2026-10-12 19:22:49'),
('3', 'Mahasiswa Uji Bima', 'demo02@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:49', '$2y$12$UExSt8fB16OTPwqLYpVGHOf0aBB2nvK1JTN5M55yxZO8yotF3HvAu', NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('4', 'Mahasiswa Uji Citra', 'demo03@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:50', '$2y$12$8zvn5HyrRrVA14Ko3y8YUOMC1vq0g0.rBO2.xX34EgM7Xn0O5eqsm', NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('5', 'Mahasiswa Uji Damar', 'demo04@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:50', '$2y$12$MSIQ7Y807MRhikueK6VfXuNWGRaZNgKHl/CXTIcre/eKuwLQ82zHi', NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('6', 'Mahasiswa Uji Elin', 'demo05@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:50', '$2y$12$UgXC9pFTuoiV9TN6TxZHPuvzUS.uGOsUzQiO1io0Je7h9YhzZ21la', NULL, '2026-10-12 19:22:50', '2026-10-12 19:22:50'),
('7', 'Mahasiswa Uji Faris', 'demo06@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:50', '$2y$12$cu8qMD5HsjUxWOKkT0ZqiuINsmbLXHfUzeV1qPUAYrcGCzrv4f3KC', NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('8', 'Mahasiswa Uji Gina', 'demo07@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:51', '$2y$12$zVGYWIyP15S1J3K3mnfuYeFMrrvG5R9th9oA.0Isf4CQx.YqKLmwS', NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('9', 'Mahasiswa Uji Hadi', 'demo08@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:51', '$2y$12$/xiy/kDpAPTHGG304VTAoO4PKLH51BpGcURyvpcfzJ13M7.ZuoWEa', NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51'),
('10', 'Mahasiswa Uji Intan', 'demo09@demo.polteksi.ac.id', 'student', '2026-10-12 19:22:51', '$2y$12$R/48TQKO4NjPVMinVzk2fegjFqg/jRr.HZEjqwE0.qOznaTUVwoj6', NULL, '2026-10-12 19:22:51', '2026-10-12 19:22:51');

UPDATE `users` SET `faculty_id` = 1;
UPDATE `users` AS `u`
INNER JOIN `student_profiles` AS `sp` ON `sp`.`user_id` = `u`.`id`
SET `u`.`study_program_id` = `sp`.`study_program_id`;

SET FOREIGN_KEY_CHECKS = 1;
