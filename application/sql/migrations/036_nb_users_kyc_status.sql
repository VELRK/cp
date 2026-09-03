-- Agent KYC review workflow: pending admin approval, rejection reason, timestamps.
ALTER TABLE `nb_users`
  ADD COLUMN IF NOT EXISTS `kyc_status` ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none' AFTER `website`,
  ADD COLUMN IF NOT EXISTS `kyc_rejection_reason` TEXT NULL DEFAULT NULL AFTER `kyc_status`,
  ADD COLUMN IF NOT EXISTS `kyc_submitted_at` DATETIME NULL DEFAULT NULL AFTER `kyc_rejection_reason`,
  ADD COLUMN IF NOT EXISTS `kyc_reviewed_at` DATETIME NULL DEFAULT NULL AFTER `kyc_submitted_at`;

-- Backfill existing agent rows where possible.
UPDATE `nb_users`
SET `kyc_status` = 'approved'
WHERE LOWER(COALESCE(`user_type`, '')) = 'agent'
  AND COALESCE(`is_verified`, 0) = 1
  AND (`kyc_status` IS NULL OR `kyc_status` = 'none');

UPDATE `nb_users`
SET `kyc_status` = 'pending',
    `kyc_submitted_at` = COALESCE(`kyc_submitted_at`, `updated_at`, `created_at`)
WHERE LOWER(COALESCE(`user_type`, '')) = 'agent'
  AND COALESCE(`is_verified`, 0) = 0
  AND TRIM(COALESCE(`business_name`, '')) <> ''
  AND TRIM(COALESCE(`aadhar_no`, '')) <> ''
  AND TRIM(COALESCE(`aadhar_file`, '')) <> ''
  AND (`kyc_status` IS NULL OR `kyc_status` = 'none');
