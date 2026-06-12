-- SEO-friendly URLs: unique slug per listing (run once on existing DBs).
SET NAMES utf8mb4;

ALTER TABLE nb_properties
  ADD COLUMN slug VARCHAR(255) NULL DEFAULT NULL COMMENT 'URL segment for /property/{slug}' AFTER title;

-- Backfill stable unique slugs before adding UNIQUE index
UPDATE nb_properties SET slug = CONCAT('property-', id) WHERE slug IS NULL OR slug = '';

CREATE UNIQUE INDEX idx_nb_properties_slug ON nb_properties (slug);
