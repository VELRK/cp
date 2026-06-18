-- Coimbatore Properties module tables (prefix nb_) — import into your CI database (e.g. property)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS nb_cities (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  state      VARCHAR(100) NOT NULL,
  is_active  TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nb_localities (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  city_id   INT NOT NULL,
  name      VARCHAR(150) NOT NULL,
  FOREIGN KEY (city_id) REFERENCES nb_cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nb_users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(150) NOT NULL,
  email        VARCHAR(200) NOT NULL UNIQUE,
  phone        VARCHAR(15) NOT NULL,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('owner','tenant','admin') NOT NULL DEFAULT 'tenant',
  status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  city_id      INT DEFAULT NULL,
  profile_pic  VARCHAR(300) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (city_id) REFERENCES nb_cities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nb_properties (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  owner_id         INT NOT NULL,
  title            VARCHAR(300) NOT NULL,
  slug             VARCHAR(255) NOT NULL COMMENT 'URL segment for /property/{slug}',
  description      TEXT,
  property_type    VARCHAR(64) NOT NULL DEFAULT 'apartment' COMMENT 'Slug: apartment, studio, office, plot, ...',
  listing_type     ENUM('rent','sale') NOT NULL,
  price            DECIMAL(12,2) NOT NULL,
  bedrooms         TINYINT UNSIGNED DEFAULT NULL,
  bathrooms        TINYINT UNSIGNED DEFAULT NULL,
  area_sqft        INT DEFAULT NULL,
  address          TEXT NOT NULL,
  locality         VARCHAR(200) NOT NULL,
  city_id          INT NOT NULL,
  latitude         DECIMAL(11,8) DEFAULT NULL COMMENT 'WGS84',
  longitude        DECIMAL(12,8) DEFAULT NULL COMMENT 'WGS84',
  google_place_id  VARCHAR(255) DEFAULT NULL COMMENT 'Google Places place_id',
  is_price_negotiable TINYINT(1) NOT NULL DEFAULT 0,
  rate_per_sqft    DECIMAL(12,2) DEFAULT NULL COMMENT 'Optional: price per sqft',
  available_from   DATE DEFAULT NULL,
  plot_length_ft   DECIMAL(10,2) DEFAULT NULL COMMENT 'Plot L (ft)',
  plot_width_ft    DECIMAL(10,2) DEFAULT NULL COMMENT 'Plot B (ft)',
  has_boundary_wall TINYINT(1) DEFAULT NULL COMMENT '1=yes 0=no',
  amenities        JSON DEFAULT NULL,
  images           JSON DEFAULT NULL,
  video_url        VARCHAR(512) DEFAULT NULL COMMENT 'YouTube/Vimeo/etc. URL',
  is_active        TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = visible on public site (admin publishes)',
  is_featured      TINYINT(1) DEFAULT 0,
  views            INT DEFAULT 0,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES nb_users(id) ON DELETE CASCADE,
  FOREIGN KEY (city_id) REFERENCES nb_cities(id),
  UNIQUE KEY idx_nb_properties_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nb_enquiries (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id    INT NOT NULL,
  property_id  INT NOT NULL,
  message      TEXT NOT NULL,
  phone        VARCHAR(15) NOT NULL,
  email        VARCHAR(200) NOT NULL,
  status       ENUM('new','read','responded','closed') DEFAULT 'new',
  admin_notes  TEXT DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES nb_users(id) ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES nb_properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nb_notifications (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  title      VARCHAR(300) NOT NULL,
  message    TEXT NOT NULL,
  type       VARCHAR(50) DEFAULT 'info',
  is_read    TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES nb_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed cities (idempotent: skip if names exist — run once on fresh DB)
INSERT IGNORE INTO nb_cities (name, state, sort_order) VALUES
('Chennai','Tamil Nadu',1), ('Mumbai','Maharashtra',2),
('Bangalore','Karnataka',3), ('Delhi','Delhi',4),
('Hyderabad','Telangana',5), ('Pune','Maharashtra',6);

INSERT IGNORE INTO nb_localities (city_id, name)
SELECT id, 'Anna Nagar' FROM nb_cities WHERE name = 'Chennai' LIMIT 1;
INSERT IGNORE INTO nb_localities (city_id, name)
SELECT id, 'T Nagar' FROM nb_cities WHERE name = 'Chennai' LIMIT 1;
INSERT IGNORE INTO nb_localities (city_id, name)
SELECT id, 'Velachery' FROM nb_cities WHERE name = 'Chennai' LIMIT 1;

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

-- Admin + demo owner (password both: Admin@123)
INSERT IGNORE INTO nb_users (name, email, phone, password, role, status, city_id)
VALUES
('Super Admin', 'admin@dreamvillamakers.com', '9999999999',
 '$2y$10$8Q88aIarCKxmr9xbI40wm.9Z.JfHyHnfXI2LgKXrp72Lj2.UmXRge', 'admin', 'approved', NULL);

INSERT IGNORE INTO nb_users (name, email, phone, password, role, status, city_id)
SELECT 'Demo Owner', 'owner@test.com', '9888888888',
 '$2y$10$8Q88aIarCKxmr9xbI40wm.9Z.JfHyHnfXI2LgKXrp72Lj2.UmXRge', 'owner', 'approved', id
FROM nb_cities WHERE name = 'Chennai' LIMIT 1;

INSERT INTO nb_properties (owner_id, title, slug, description, property_type, listing_type, price, bedrooms, bathrooms, area_sqft, address, locality, city_id, latitude, longitude, amenities, images, is_active, is_featured, views)
SELECT u.id, 'Sea View 2BHK Apartment', 'sea-view-2bhk-apartment-omr', 'Spacious flat near OMR.', 'apartment', 'rent', 28000.00, 2, 2, 1200,
'Plot 12, OMR', 'Sholinganallur', c.id, 12.9516, 80.2442, '["Lift","Parking"]', '[]', 1, 1, 0
FROM nb_users u JOIN nb_cities c ON c.name = 'Chennai' WHERE u.email = 'owner@test.com' LIMIT 1;
