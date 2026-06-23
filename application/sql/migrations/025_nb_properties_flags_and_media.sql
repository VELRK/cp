-- Property listing flags + brochure / audio notes (per listing)
-- Run each statement once; ignore "Duplicate column" if re-applying.
ALTER TABLE `nb_properties`
  ADD COLUMN `is_recommended` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`;
ALTER TABLE `nb_properties`
  ADD COLUMN `is_newly_launched` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_recommended`;
ALTER TABLE `nb_properties`
  ADD COLUMN `is_verified_property` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_newly_launched`;
ALTER TABLE `nb_properties`
  ADD COLUMN `is_premium` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_verified_property`;
ALTER TABLE `nb_properties`
  ADD COLUMN `brochure_url` VARCHAR(512) DEFAULT NULL AFTER `video_url`;
ALTER TABLE `nb_properties`
  ADD COLUMN `audio_notes_url` VARCHAR(512) DEFAULT NULL AFTER `brochure_url`;
