-- OTP fields for phone login on nb_users (owner / tenant / agent).

ALTER TABLE `nb_users`
  ADD COLUMN IF NOT EXISTS `otp` VARCHAR(10) NULL AFTER `profile_pic`,
  ADD COLUMN IF NOT EXISTS `otp_expires_at` DATETIME NULL AFTER `otp`;
