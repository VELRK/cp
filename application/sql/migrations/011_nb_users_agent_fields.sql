-- Add agent/customer profile fields for registration flow.
-- Safe to re-run: each column is added only when missing.

ALTER TABLE nb_users
  ADD COLUMN IF NOT EXISTS user_type ENUM('agent','customer') NOT NULL DEFAULT 'customer' AFTER role,
  ADD COLUMN IF NOT EXISTS aadhar_no VARCHAR(20) NULL AFTER city_id,
  ADD COLUMN IF NOT EXISTS aadhar_file VARCHAR(300) NULL AFTER aadhar_no,
  ADD COLUMN IF NOT EXISTS experience_years TINYINT UNSIGNED NULL AFTER aadhar_file;

