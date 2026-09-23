-- Store full Google Maps share / embed URLs (often longer than VARCHAR 500).
ALTER TABLE `nb_properties` MODIFY COLUMN `map_url` TEXT NULL;
ALTER TABLE `nb_properties` MODIFY COLUMN `location` TEXT NULL;
