-- Allow user-side CRUD ownership for live updates.

ALTER TABLE `live_updates`
  ADD COLUMN IF NOT EXISTS `userId` VARCHAR(100) NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `updatedAt` DATETIME NULL AFTER `createdAt`,
  ADD KEY `idx_live_updates_userId` (`userId`);

