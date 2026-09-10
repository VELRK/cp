-- Reason stored when admin rejects a listing (owner email + panel).
ALTER TABLE `nb_properties`
  ADD COLUMN `rejection_reason` TEXT NULL DEFAULT NULL AFTER `is_active`;
