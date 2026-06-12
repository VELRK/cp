-- Optional screenshot / attachment for feedback (web + mobile API).

ALTER TABLE `feedbacks`
  ADD COLUMN `image` VARCHAR(500) NULL AFTER `description`;
