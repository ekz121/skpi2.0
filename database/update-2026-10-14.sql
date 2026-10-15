-- Pembaruan database untuk instalasi SKPI yang sudah berjalan.
-- Impor satu kali melalui phpMyAdmin pada database u841207907_skpi.

SET NAMES utf8mb4;
SET time_zone = '+07:00';

ALTER TABLE `skpi_requests`
  MODIFY `status` enum('pending','revision','issued','failed','rejected','withdrawn') NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS `skpi_withdrawals` (
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

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_14_000600_add_skpi_withdrawal_history', `batches`.`next_batch`
FROM (
  SELECT COALESCE(MAX(`batch`), 0) + 1 AS `next_batch`
  FROM `migrations`
) AS `batches`
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations`
  WHERE `migration` = '2026_10_14_000600_add_skpi_withdrawal_history'
);
