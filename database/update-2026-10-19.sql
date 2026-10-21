-- Pembaruan 19 Oktober 2026: jenis sertifikat khusus dan template SKPI tanpa bobot.
-- Jalankan satu kali pada database produksi yang sudah memakai update-2026-10-15.sql.

ALTER TABLE `submissions`
    ADD COLUMN `custom_activity_type` varchar(180) NULL AFTER `activity_rule_id`;

INSERT INTO `activity_rules`
    (`category`, `subcategory`, `activity_type`, `level`, `achievement`, `duration_label`, `points`, `evidence_label`, `is_mandatory`, `is_active`, `created_at`, `updated_at`)
SELECT
    'Sertifikat Magang', 'Magang', 'Magang Industri', NULL, NULL, NULL, 60, 'Sertifikat magang', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `activity_rules`
    WHERE `category` = 'Sertifikat Magang' AND `activity_type` = 'Magang Industri'
);

INSERT INTO `activity_rules`
    (`category`, `subcategory`, `activity_type`, `level`, `achievement`, `duration_label`, `points`, `evidence_label`, `is_mandatory`, `is_active`, `created_at`, `updated_at`)
SELECT
    'Sertifikat Lain-lain', 'Lain-lain', 'Lain-lain', NULL, NULL, NULL, 0, 'Sertifikat atau dokumen resmi', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `activity_rules`
    WHERE `category` = 'Sertifikat Lain-lain' AND `activity_type` = 'Lain-lain'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_19_000800_add_special_certificate_types', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` AS `existing`
    WHERE `existing`.`migration` = '2026_10_19_000800_add_special_certificate_types'
);
