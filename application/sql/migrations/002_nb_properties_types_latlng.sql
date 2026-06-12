-- Upgrade nb_properties for extended listings (run on existing DBs).
-- Step 1: always safe to re-run MODIFY (idempotent types/lat/lng).
-- Step 2: run ADD COLUMN only once; if you see "Duplicate column", skip it.

ALTER TABLE nb_properties
  MODIFY COLUMN property_type VARCHAR(64) NOT NULL DEFAULT 'apartment',
  MODIFY COLUMN latitude DECIMAL(11,8) DEFAULT NULL COMMENT 'WGS84',
  MODIFY COLUMN longitude DECIMAL(12,8) DEFAULT NULL COMMENT 'WGS84';

ALTER TABLE nb_properties
  ADD COLUMN google_place_id VARCHAR(255) DEFAULT NULL COMMENT 'Google Places place_id' AFTER longitude;
