-- Extra listing fields (listing extras: negotiable price, rate/sqft, plot dimensions, availability).
-- Run after 002. Safe to run once; skip lines that error if columns already exist.

ALTER TABLE nb_properties
  ADD COLUMN is_price_negotiable TINYINT(1) NOT NULL DEFAULT 0 AFTER google_place_id,
  ADD COLUMN rate_per_sqft DECIMAL(12,2) DEFAULT NULL COMMENT 'Derived or entered' AFTER is_price_negotiable,
  ADD COLUMN available_from DATE DEFAULT NULL AFTER rate_per_sqft,
  ADD COLUMN plot_length_ft DECIMAL(10,2) DEFAULT NULL COMMENT 'Plot length (feet)' AFTER available_from,
  ADD COLUMN plot_width_ft DECIMAL(10,2) DEFAULT NULL COMMENT 'Plot width (feet)' AFTER plot_length_ft,
  ADD COLUMN has_boundary_wall TINYINT(1) DEFAULT NULL COMMENT '1=yes 0=no NULL=n/a' AFTER plot_width_ft;
