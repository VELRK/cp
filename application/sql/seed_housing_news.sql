-- Housing news demo rows with images (paths under project root; API serves full URLs via base_url).
-- Uses bundled static assets so images load without external CDN.
-- Run: mysql -u root -p property < application/sql/seed_housing_news.sql
-- Safe re-run: removes rows where title starts with [SEED]

SET NAMES utf8mb4;

DELETE FROM `housing_news` WHERE `title` LIKE '[SEED] %';

INSERT INTO `housing_news` (`title`, `subtitle`, `description`, `multiImages`, `authorName`, `category`, `createdAt`) VALUES
(
  '[SEED] Metro links and micro-markets to watch',
  'Infrastructure',
  'New transit corridors often lift nearby residential catchments before commercial nodes mature. Track completion timelines and land-use notifications alongside ticketed access.',
  '["assets/img/nb-placeholder-property.svg","assets/images/partner/partner1.svg","assets/images/partner/partner2.svg"]',
  'Property Desk',
  'market',
  NOW()
),
(
  '[SEED] Rent agreements: stamp duty basics',
  'Tips for tenants',
  'Align registered rent values with local circle guidance, capture maintenance caps in writing, and keep digital scans of ID proofs with the executed deed.',
  '["assets/images/partner/partner3.svg","assets/images/partner/partner4.svg"]',
  'Legal Edit',
  'tips',
  NOW()
),
(
  '[SEED] RERA filing windows for new projects',
  'Compliance calendar',
  'Developers should reconcile quarterly sales disclosures with authority portals; buyers can verify QR-linked project pages before token advances.',
  '["assets/images/partner/partner5.svg","assets/images/partner/partner6.svg","assets/img/nb-placeholder-property.svg"]',
  'Regulatory Brief',
  'legal',
  NOW()
),
(
  '[SEED] Affordable inventory in emerging suburbs',
  'Price discovery',
  'Smaller format units with clubbed amenities are seeing faster absorption where job hubs added shuttle links within the last 18 months.',
  '["assets/img/nb-placeholder-property.svg","assets/images/partner/partner1.svg"]',
  'Market Scan',
  'market',
  NOW()
);
