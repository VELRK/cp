-- Slugs are generated from listing title (nb_slugify + unique_slug in PHP), not raw IDs.
-- After legacy backfill (property-{id}), rebuild all slugs from titles from project root:
--   php index.php cli_tool backfill_property_slugs
SELECT 1;
