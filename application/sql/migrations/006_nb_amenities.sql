-- Master amenities list (admin CRUD). Property JSON `amenities` stores selected names.
CREATE TABLE IF NOT EXISTS nb_amenities (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  slug        VARCHAR(140) NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_nb_amenities_slug (slug),
  UNIQUE KEY uq_nb_amenities_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO nb_amenities (name, slug, sort_order, is_active) VALUES
('Lift', 'lift', 1, 1),
('Parking', 'parking', 2, 1),
('Security', 'security', 3, 1),
('Power Backup', 'power-backup', 4, 1),
('Swimming Pool', 'swimming-pool', 5, 1),
('Gym', 'gym', 6, 1),
('Garden', 'garden', 7, 1),
('Water Supply', 'water-supply', 8, 1),
('Gas Pipeline', 'gas-pipeline', 9, 1),
('Pet Friendly', 'pet-friendly', 10, 1);
