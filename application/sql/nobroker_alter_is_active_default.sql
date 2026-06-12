-- Run once on existing DBs (optional): align default with new installs — does not change existing rows.
ALTER TABLE nb_properties
  MODIFY COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0
  COMMENT '1 = visible on public site (admin publishes)';
