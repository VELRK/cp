-- Full demo data: users, one listing per property type, sample enquiries & notifications.
-- Password for all demo accounts: Admin@123
-- Run: mysql -u root property < application/sql/seed_nb_demo_full.sql
-- Safe re-run: removes rows where title LIKE '[DEMO] %' or notifications title LIKE '[DEMO] %'

SET NAMES utf8mb4;
SET @DEMO_PW = '$2y$10$8Q88aIarCKxmr9xbI40wm.9Z.JfHyHnfXI2LgKXrp72Lj2.UmXRge';

DELETE e FROM nb_enquiries e
  INNER JOIN nb_properties p ON p.id = e.property_id
  WHERE p.title LIKE '[DEMO] %';
DELETE FROM nb_properties WHERE title LIKE '[DEMO] %';
DELETE FROM nb_notifications WHERE title LIKE '[DEMO] %';

INSERT IGNORE INTO nb_users (name, email, phone, password, role, status, city_id)
SELECT 'Demo Tenant', 'tenant@test.com', '9777777777', @DEMO_PW, 'tenant', 'approved', id FROM nb_cities WHERE name = 'Chennai' LIMIT 1;
INSERT IGNORE INTO nb_users (name, email, phone, password, role, status, city_id)
SELECT 'Owner Mumbai', 'owner_mumbai@test.com', '9766666666', @DEMO_PW, 'owner', 'approved', id FROM nb_cities WHERE name = 'Mumbai' LIMIT 1;

SET @chennai := (SELECT id FROM nb_cities WHERE name = 'Chennai' LIMIT 1);
SET @mumbai := (SELECT id FROM nb_cities WHERE name = 'Mumbai' LIMIT 1);
SET @blore := (SELECT id FROM nb_cities WHERE name = 'Bangalore' LIMIT 1);
SET @hyd := (SELECT id FROM nb_cities WHERE name = 'Hyderabad' LIMIT 1);
SET @pune := (SELECT id FROM nb_cities WHERE name = 'Pune' LIMIT 1);
SET @delhi := (SELECT id FROM nb_cities WHERE name = 'Delhi' LIMIT 1);

SET @own1 := (SELECT id FROM nb_users WHERE email = 'owner@test.com' LIMIT 1);
SET @own2 := (SELECT id FROM nb_users WHERE email = 'owner_mumbai@test.com' LIMIT 1);
SET @tenant := (SELECT id FROM nb_users WHERE email = 'tenant@test.com' LIMIT 1);

INSERT INTO nb_properties (
  owner_id, title, slug, description, property_type, listing_type, price,
  bedrooms, bathrooms, area_sqft, address, locality, city_id,
  latitude, longitude, google_place_id,
  is_price_negotiable, rate_per_sqft, available_from, plot_length_ft, plot_width_ft, has_boundary_wall,
  amenities, images, is_active, is_featured, views
) VALUES
(@own1, '[DEMO] Premium 3BHK Near OMR', 'demo-premium-3bhk-near-omr', 'Corner unit, sea breeze, two covered parking.', 'apartment', 'sale', 12500000.00, 3, 3, 1650,
 'Tower A, OMR', 'Sholinganallur', @chennai, 12.9490, 80.2380, NULL, 1, 7575.00, '2026-02-01', NULL, NULL, NULL,
 '["Lift","Parking","Security","Power Backup"]', '[]', 1, 1, 12),

(@own1, '[DEMO] Studio For Rent Indiranagar', 'demo-studio-rent-indiranagar', 'Furnished studio walk to metro.', 'studio', 'rent', 22000.00, 1, 1, 450,
 '12th Main', 'Indiranagar', @blore, 12.9716, 77.6412, NULL, 0, NULL, '2026-01-15', NULL, NULL, NULL,
 '["Furnished","Power Backup"]', '[]', 1, 0, 5),

(@own2, '[DEMO] Independent House Bandra', 'demo-independent-house-bandra', 'G+2 with terrace.', 'house', 'sale', 45000000.00, 4, 4, 2400,
 'Near Carter Road', 'Bandra West', @mumbai, 19.0596, 72.8295, NULL, 1, NULL, NULL, NULL, NULL, NULL,
 '["Parking","Garden"]', '[]', 1, 1, 8),

(@own1, '[DEMO] Villa Gated Community ECR', 'demo-villa-gated-community-ecr', 'Private pool, 24x7 security.', 'villa', 'sale', 35000000.00, 5, 5, 4200,
 'ECR Lane 4', 'Neelankarai', @chennai, 12.9492, 80.2540, NULL, 0, 8333.00, NULL, NULL, NULL, NULL,
 '["Swimming Pool","Security","Garden"]', '[]', 1, 0, 3),

(@own1, '[DEMO] Independent Floor Dwarka', 'demo-independent-floor-dwarka', 'Single family one floor.', 'independent_floor', 'sale', 18500000.00, 3, 3, 1800,
 'Sector 12', 'Dwarka', @delhi, 28.5921, 77.0460, NULL, 0, NULL, NULL, NULL, NULL, NULL,
 '["Lift","Parking"]', '[]', 1, 0, 2),

(@own2, '[DEMO] Commercial Showroom Lower Parel', 'demo-commercial-showroom-lower-parel', 'High street frontage.', 'commercial', 'sale', 80000000.00, NULL, 4, 3500,
 'High Street', 'Lower Parel', @mumbai, 18.9984, 72.8277, NULL, 1, NULL, '2026-03-01', NULL, NULL, NULL,
 '["Parking","Security"]', '[]', 1, 0, 1),

(@own1, '[DEMO] Office Space Hitech City', 'demo-office-space-hitech-city', 'Plug and play IT office.', 'office', 'rent', 85000.00, NULL, 2, 2200,
 'Mindspace', 'Hitech City', @hyd, 17.4486, 78.3908, NULL, 0, NULL, '2026-01-01', NULL, NULL, NULL,
 '["Power Backup","Security"]', '[]', 1, 0, 4),

(@own1, '[DEMO] Retail Shop Koramangala', 'demo-retail-shop-koramangala', 'Corner shop suitable F&B.', 'retail', 'rent', 65000.00, NULL, 2, 900,
 '5th Block', 'Koramangala', @blore, 12.9352, 77.6245, NULL, 1, NULL, NULL, NULL, NULL, NULL,
 '["Parking"]', '[]', 1, 0, 0),

(@own2, '[DEMO] Warehouse Bhiwandi', 'demo-warehouse-bhiwandi', 'Loading bay, 18ft height.', 'warehouse', 'rent', 120000.00, NULL, 2, 8000,
 'MIDC', 'Bhiwandi', @mumbai, 19.3002, 73.0583, NULL, 0, NULL, NULL, NULL, NULL, NULL,
 '[]', '[]', 1, 0, 0),

(@own1, '[DEMO] Plot For Sale In Pallikaranai', 'demo-plot-sale-pallikaranai', 'Vacant CMDA plot, east facing. Eden Castle vicinity.', 'plot', 'sale', 7500000.00,
 NULL, NULL, 2000, 'Eden Castle, Pari Nagar', 'Pallikaranai', @chennai, 12.9430, 80.2010, NULL,
 1, 3750.00, '2026-01-22', 50.00, 40.00, 0, '[]', '[]', 1, 1, 42),

(@own1, '[DEMO] Farmhouse Off GST Road', 'demo-farmhouse-off-gst-road', 'Mango grove, bore well.', 'farmhouse', 'sale', 22000000.00, 4, 3, 6000,
 'Village road', 'Singaperumal Koil', @chennai, 12.7594, 79.9990, NULL, 0, NULL, NULL, NULL, NULL, NULL,
 '["Garden","Water Supply"]', '[]', 1, 0, 0),

(@own1, '[DEMO] PG For Men Velachery', 'demo-pg-for-men-velachery', 'Food included, Wi‑Fi.', 'pg', 'rent', 9000.00, 1, 4, 1200,
 'Near Phoenix', 'Velachery', @chennai, 12.9815, 80.2209, NULL, 0, NULL, NULL, NULL, NULL, NULL,
 '["Security","WiFi"]', '[]', 1, 0, 6),

(@own1, '[DEMO] Shared Flat Flatmates HSR', 'demo-shared-flat-flatmates-hsr', '2 beds in 3BHK shared.', 'shared_flat', 'rent', 14000.00, 1, 2, 1100,
 'Sector 2', 'HSR Layout', @blore, 12.9116, 77.6412, NULL, 0, NULL, NULL, NULL, NULL, NULL,
 '["Gym","Parking"]', '[]', 1, 0, 2),

(@own2, '[DEMO] Serviced Apartment Worli', 'demo-serviced-apartment-worli', 'Hotel-style monthly.', 'serviced_apartment', 'rent', 95000.00, 2, 2, 1100,
 'Tower B', 'Worli', @mumbai, 18.9986, 72.8174, NULL, 1, NULL, NULL, NULL, NULL, NULL,
 '["Lift","Gym","Security"]', '[]', 1, 0, 1),

(@own1, '[DEMO] Others Land Parcel Sholinganallur', 'demo-others-land-parcel-sholinganallur', 'Agricultural conversion in progress.', 'others', 'sale', 15000000.00,
 NULL, NULL, 5000, 'Survey 124', 'Sholinganallur', @chennai, 12.9550, 80.2450, NULL,
 1, 3000.00, NULL, 100.00, 50.00, NULL, '[]', '[]', 1, 0, 0);

INSERT INTO nb_enquiries (tenant_id, property_id, message, phone, email, status)
SELECT @tenant, p.id, 'I would like to schedule a site visit this weekend.', '9777777777', 'tenant@test.com', 'new'
FROM nb_properties p WHERE p.title = '[DEMO] Plot For Sale In Pallikaranai' LIMIT 1;

INSERT INTO nb_enquiries (tenant_id, property_id, message, phone, email, status)
SELECT @tenant, p.id, 'Please share best price for cash payment.', '9777777777', 'tenant@test.com', 'read'
FROM nb_properties p WHERE p.title = '[DEMO] Premium 3BHK Near OMR' LIMIT 1;

INSERT INTO nb_enquiries (tenant_id, property_id, message, phone, email, status)
SELECT @tenant, p.id, 'Is the studio still available for Feb move-in?', '9777777777', 'tenant@test.com', 'new'
FROM nb_properties p WHERE p.title = '[DEMO] Studio For Rent Indiranagar' LIMIT 1;

INSERT INTO nb_notifications (user_id, title, message, type, is_read)
SELECT id, '[DEMO] New matches', 'We found 3 new listings in your saved area.', 'info', 0
FROM nb_users WHERE email = 'tenant@test.com' LIMIT 1;

INSERT INTO nb_notifications (user_id, title, message, type, is_read)
SELECT u.id, '[DEMO] Lead reminder', 'Follow up on enquiries from last week.', 'info', 1
FROM nb_users u WHERE u.email = 'owner@test.com' LIMIT 1;
