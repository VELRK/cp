-- Main / sub property type hierarchy (single table).
ALTER TABLE `nb_property_types`
  ADD COLUMN `parent_id` INT(11) DEFAULT NULL AFTER `id`;

ALTER TABLE `nb_property_types`
  ADD KEY `idx_nb_property_types_parent` (`parent_id`);
