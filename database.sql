CREATE DATABASE IF NOT EXISTS rss_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rss_manager;

CREATE TABLE posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(500) NOT NULL,
  content TEXT NOT NULL,
  image_url VARCHAR(1000) DEFAULT NULL,
  char_count INT UNSIGNED NOT NULL DEFAULT 0,
  pub_date DATETIME NOT NULL,
  priority INT UNSIGNED NOT NULL DEFAULT 0,
  feed_url VARCHAR(1000) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_priority (priority),
  INDEX idx_pub_date (pub_date),
  INDEX idx_feed_url (feed_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_platforms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  icon VARCHAR(100) DEFAULT NULL,
  color VARCHAR(20) DEFAULT NULL,
  max_chars INT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE post_platforms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NOT NULL,
  platform_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uniq_post_platform (post_id, platform_id),
  INDEX idx_platform (platform_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO social_platforms (name, slug, icon, color, max_chars) VALUES
('Facebook','facebook','bi-facebook','#1877F2',63206),
('X (Twitter)','x','bi-twitter-x','#000000',280),
('LinkedIn','linkedin','bi-linkedin','#0A66C2',3000),
('Instagram','instagram','bi-instagram','#E4405F',2200),
('TikTok','tiktok','bi-tiktok','#000000',2200),
('Threads','threads','bi-threads','#000000',500),
('Bluesky','bluesky','bi-cloud','#0085FF',300);
