-- Agent KYC: business name (required in app flow), optional website.
ALTER TABLE `nb_users`
  ADD COLUMN IF NOT EXISTS `business_name` VARCHAR(200) NULL DEFAULT NULL AFTER `experience_years`,
  ADD COLUMN IF NOT EXISTS `website` VARCHAR(500) NULL DEFAULT NULL AFTER `business_name`;
