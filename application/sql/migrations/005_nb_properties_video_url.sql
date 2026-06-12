-- Optional tour / walkthrough video (YouTube, Vimeo, etc.) pasted as URL
SET NAMES utf8mb4;
ALTER TABLE nb_properties
  ADD COLUMN video_url VARCHAR(512) NULL DEFAULT NULL COMMENT 'YouTube/Vimeo/etc. embed URL' AFTER images;
