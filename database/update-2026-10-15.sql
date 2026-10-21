-- Pembaruan manajemen user untuk instalasi SKPI yang sudah berjalan.
-- Impor satu kali melalui phpMyAdmin pada database u841207907_skpi.
-- Buat backup database sebelum menjalankan file ini.

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `faculties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculties_code_unique` (`code`),
  UNIQUE KEY `faculties_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faculties` (`code`, `name`, `created_at`, `updated_at`)
VALUES ('POLTEKSI', 'Politeknik Semen Indonesia', NOW(), NOW());

ALTER TABLE `study_programs`
  ADD `faculty_id` bigint(20) unsigned DEFAULT NULL AFTER `id`,
  ADD KEY `study_programs_faculty_id_foreign` (`faculty_id`),
  ADD CONSTRAINT `study_programs_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL;

UPDATE `study_programs`
SET `faculty_id` = (SELECT `id` FROM `faculties` WHERE `code` = 'POLTEKSI' LIMIT 1);

ALTER TABLE `users`
  MODIFY `role` enum('student','program_verifier','admin','leader') NOT NULL DEFAULT 'student',
  ADD `faculty_id` bigint(20) unsigned DEFAULT NULL AFTER `role`,
  ADD `study_program_id` bigint(20) unsigned DEFAULT NULL AFTER `faculty_id`,
  ADD `is_active` tinyint(1) NOT NULL DEFAULT 1 AFTER `study_program_id`,
  ADD `deactivated_at` timestamp NULL DEFAULT NULL AFTER `is_active`,
  ADD `deactivated_by` bigint(20) unsigned DEFAULT NULL AFTER `deactivated_at`,
  ADD KEY `users_faculty_id_foreign` (`faculty_id`),
  ADD KEY `users_study_program_id_foreign` (`study_program_id`),
  ADD KEY `users_is_active_index` (`is_active`),
  ADD KEY `users_deactivated_by_foreign` (`deactivated_by`),
  ADD CONSTRAINT `users_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_study_program_id_foreign` FOREIGN KEY (`study_program_id`) REFERENCES `study_programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_deactivated_by_foreign` FOREIGN KEY (`deactivated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

UPDATE `users`
SET `faculty_id` = (SELECT `id` FROM `faculties` WHERE `code` = 'POLTEKSI' LIMIT 1);

UPDATE `users` AS `u`
INNER JOIN `student_profiles` AS `sp` ON `sp`.`user_id` = `u`.`id`
SET `u`.`study_program_id` = `sp`.`study_program_id`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_15_000700_add_user_management', `batches`.`next_batch`
FROM (
  SELECT COALESCE(MAX(`batch`), 0) + 1 AS `next_batch`
  FROM `migrations`
) AS `batches`
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations`
  WHERE `migration` = '2026_10_15_000700_add_user_management'
);

SET FOREIGN_KEY_CHECKS = 1;
