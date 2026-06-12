-- Backfill NULL / blank / whitespace-only slugs (safe id-based segment, unique per row).
-- Run once if legacy rows bypassed slug generation. Safe to re-run: only touches empty slugs.
SET NAMES utf8mb4;

UPDATE nb_properties
SET slug = CONCAT('property-', id)
WHERE slug IS NULL OR TRIM(slug) = '';
