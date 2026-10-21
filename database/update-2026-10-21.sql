-- Pembaruan aman 21 Oktober 2026 untuk database SKPI yang sudah berjalan.
-- File ini tidak menghapus akun, profil, sertifikat, atau SKPI.
-- Buat backup database, lalu impor satu kali melalui phpMyAdmin.

SET NAMES utf8mb4;
SET time_zone = '+07:00';

UPDATE `users` AS `u`
INNER JOIN `student_profiles` AS `sp` ON `sp`.`user_id` = `u`.`id`
INNER JOIN `study_programs` AS `p` ON `p`.`id` = `sp`.`study_program_id`
SET
    `u`.`study_program_id` = `sp`.`study_program_id`,
    `u`.`faculty_id` = `p`.`faculty_id`,
    `u`.`updated_at` = NOW()
WHERE `u`.`role` = 'student';

UPDATE `student_registries` AS `sr`
INNER JOIN `student_profiles` AS `sp` ON `sp`.`nim` = `sr`.`nim`
INNER JOIN `users` AS `u` ON `u`.`id` = `sp`.`user_id` AND `u`.`role` = 'student'
LEFT JOIN `student_registries` AS `linked` ON `linked`.`user_id` = `u`.`id` AND `linked`.`id` <> `sr`.`id`
SET
    `sr`.`user_id` = `u`.`id`,
    `sr`.`updated_at` = NOW()
WHERE `sr`.`user_id` IS NULL
  AND `linked`.`id` IS NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_21_000900_reconcile_student_accounts', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` AS `existing`
    WHERE `existing`.`migration` = '2026_10_21_000900_reconcile_student_accounts'
);

-- Ringkasan pemeriksaan. total_akun_mahasiswa dapat berbeda dari total_daftar_akademik
-- jika ada akun demo atau akun lama yang NIM-nya tidak terdapat pada daftar akademik.
SELECT
    (SELECT COUNT(*) FROM `student_registries`) AS `total_daftar_akademik`,
    (SELECT COUNT(*) FROM `student_registries` WHERE `user_id` IS NOT NULL) AS `daftar_sudah_mendaftar`,
    (SELECT COUNT(*) FROM `users` WHERE `role` = 'student') AS `total_akun_mahasiswa`,
    (SELECT COUNT(*) FROM `users` AS `u` WHERE `u`.`role` = 'student' AND NOT EXISTS (
        SELECT 1 FROM `student_registries` AS `sr` WHERE `sr`.`user_id` = `u`.`id`
    )) AS `akun_belum_tertaut`;
