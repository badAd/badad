-- Instructions:
-- 1. Run all this script
-- 2. updatecatids.php (for live site, not needed for this dummy site)

-- Projects & Partners
CREATE DATABASE badadlistdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON badadlistdb.* TO 'badadlistdb'@'localhost' IDENTIFIED BY 'badadlistdbpassword';
FLUSH PRIVILEGES;

USE badadlistdb;

CREATE TABLE IF NOT EXISTS `seen_ad_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `source` ENUM('listed', 'view', 'cat', 'tag', 'search') NOT NULL,
  `keytext` TINYTEXT DEFAULT NULL,
  `subkey` TINYTEXT DEFAULT NULL,
  `key_id` SMALLINT UNSIGNED DEFAULT NULL,
  `filter` TINYTEXT DEFAULT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clicked_ad_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `source` ENUM('listed', 'view', 'cat', 'tag', 'search') NOT NULL,
  `keytext` TINYTEXT DEFAULT NULL,
  `subkey` TINYTEXT DEFAULT NULL,
  `key_id` SMALLINT UNSIGNED DEFAULT NULL,
  `filter` TINYTEXT DEFAULT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clicked_pod_ad_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `seen_partnersite_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `partnersite_id` INT UNSIGNED DEFAULT NULL,
  `num_ads_set` TINYINT UNSIGNED NOT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clicked_badad_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `partnersite_id` INT UNSIGNED DEFAULT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `global_subcat_ids` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_id` INT UNSIGNED NOT NULL,
  `subcat_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- updatecatids.php will populate the global_subcat_ids table
-- for testing:
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '11');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '12');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '13');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '14');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '15');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '16');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '17');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '18');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('1', '19');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '11');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '12');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '13');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '14');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '15');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '16');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('2', '17');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('3', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '11');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '12');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '13');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('4', '14');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '11');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '12');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '13');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '14');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '15');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '16');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '17');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '18');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('5', '19');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('6', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('7', '11');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('8', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '7');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '8');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '9');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('9', '10');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '1');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '2');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '3');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '4');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '5');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '6');
INSERT INTO global_subcat_ids (cat_id, subcat_id) VALUES ('10', '7');

CREATE TABLE IF NOT EXISTS `listads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `ad_lang` ENUM('en', 'zh', 'fr', 'nl', 'cs', 'de', 'he', 'ar', 'es', 'pt', 'ru', 'ms', 'el', 'jp', 'ko', 'sv', 'fi', 'it', 'nv', 'hi') NOT NULL,
  `global_subcat_id` INT UNSIGNED NOT NULL,
  `serialno` VARCHAR(255) NOT NULL,
  `listed_count` BIGINT UNSIGNED DEFAULT 0,
  `view_count` BIGINT UNSIGNED DEFAULT 0,
  `cat_count` BIGINT UNSIGNED DEFAULT 0,
  `tag_count` BIGINT UNSIGNED DEFAULT 0,
  `search_count` BIGINT UNSIGNED DEFAULT 0,
  `listed_click_count` BIGINT UNSIGNED DEFAULT 0,
  `view_click_count` BIGINT UNSIGNED DEFAULT 0,
  `tag_click_count` BIGINT UNSIGNED DEFAULT 0,
  `cat_click_count` BIGINT UNSIGNED DEFAULT 0,
  `search_click_count` BIGINT UNSIGNED DEFAULT 0,
  `pub_status` ENUM('live', 'expired', 'dead') NOT NULL,
  `list_wk_count` BIGINT DEFAULT 0,
  `epoch_wk_reset` BIGINT NOT NULL,
  `epoch_created` BIGINT NOT NULL,
  `epoch_starts` BIGINT NOT NULL,
  `epoch_dead` BIGINT NOT NULL,
  `ad_content_hdng` LONGTEXT NOT NULL,
  `ad_content_dscr` LONGTEXT NOT NULL,
  `ad_content_info` LONGTEXT NOT NULL,
  `ad_content_pyrt` LONGTEXT NOT NULL,
  `ad_content_cntc` LONGTEXT NOT NULL,
  `ad_content_bizn` LONGTEXT DEFAULT NULL,
  `ad_biz_listing` ENUM('non','biz') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `partnersites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `domain` VARCHAR(255) NOT NULL,
  `source` TEXT DEFAULT NULL,
  `nickname` VARCHAR(255) DEFAULT NULL,
  `allow_subdomain` BOOLEAN NOT NULL DEFAULT true,
  `horizontal_inline` BOOLEAN NOT NULL DEFAULT false,
  `num_ads_to_show` TINYINT UNSIGNED DEFAULT 2,
  `global_subcat_ids` LONGTEXT DEFAULT NULL,
  `auto_add_new_cat` BOOLEAN NOT NULL DEFAULT true,
  `site_lang` ENUM('en', 'zh', 'fr', 'nl', 'cs', 'de', 'he', 'ar', 'es', 'pt', 'ru', 'ms', 'el', 'jp', 'ko', 'sv', 'fi', 'it', 'nv', 'hi') NOT NULL,
  `listed_badad_count` BIGINT UNSIGNED DEFAULT 0,
  `listed_ad_count` BIGINT UNSIGNED DEFAULT 0,
  `clicked_badad_count` BIGINT UNSIGNED DEFAULT 0,
  `clicked_listed_count` BIGINT UNSIGNED DEFAULT 0,
  `useable` ENUM('live', 'off', 'failed', 'deleted') NOT NULL,
  `serial_no` VARCHAR(255) NOT NULL,
  `badadref_no` VARCHAR(255) NOT NULL,
  `date_tallied` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `papp_key` VARCHAR(255) DEFAULT NULL,
  `call_key` VARCHAR(255) DEFAULT NULL,
  `dev_authorized_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `type` ENUM('site', 'app', 'podcast') NOT NULL,
  `connected_callback` VARCHAR(255) DEFAULT NULL,
  `directory_listed` ENUM('no', 'listed') NOT NULL,
  `directory_name` VARCHAR(255) DEFAULT NULL,
  `directory_url` LONGTEXT DEFAULT NULL,
  `stitcher_url` LONGTEXT DEFAULT NULL,
  `spotify_url` LONGTEXT DEFAULT NULL,
  `apple_url` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmpartnerchange` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `confirmkey` VARCHAR(255) DEFAULT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmdelsite` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `siteid` INT UNSIGNED NOT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `confirmkey` VARCHAR(255) DEFAULT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `deletedpartnersites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `siteid` INT UNSIGNED NOT NULL,
  `userid` BIGINT UNSIGNED NOT NULL,
  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `siteid` (`siteid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `devkeys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `domain` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `callback` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('test', 'live', 'deleted') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `test_pub_key` VARCHAR(255) DEFAULT NULL,
  `test_sec_key` VARCHAR(255) DEFAULT NULL,
  `live_pub_key` VARCHAR(255) DEFAULT NULL,
  `live_sec_key` VARCHAR(255) DEFAULT NULL,
  `old_pub_key` VARCHAR(255) DEFAULT NULL,
  `old_sec_key` VARCHAR(255) DEFAULT NULL,
  `date_newkeys` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `use_custom_callback` BOOLEAN DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `olddevkeys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `devkey_id` INT UNSIGNED NOT NULL,
  `old_pub_key` VARCHAR(255) DEFAULT NULL,
  `old_sec_key` VARCHAR(255) DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `deleteddevkeys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `devkey_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `domain` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `live_pub_key` VARCHAR(255) DEFAULT NULL,
  `live_sec_key` VARCHAR(255) DEFAULT NULL,
  `test_pub_key` VARCHAR(255) DEFAULT NULL,
  `test_sec_key` VARCHAR(255) DEFAULT NULL,
  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmdevappchange` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `confirmkey` VARCHAR(255) DEFAULT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmdeldevapp` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `appid` INT UNSIGNED NOT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `confirmkey` VARCHAR(255) DEFAULT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `deleteddevaps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `appid` INT UNSIGNED NOT NULL,
  `userid` BIGINT UNSIGNED NOT NULL,
  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `join_rank` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `date_joined` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Main Users & Ads
CREATE DATABASE badaddb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON badaddb.* TO 'badaddb'@'localhost' IDENTIFIED BY 'badaddbpassword';
FLUSH PRIVILEGES;

USE badaddb;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `role` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `initial` CHAR NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `initial` (`initial`),
  UNIQUE KEY `role` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`role`, `slug`, `initial`, `description`) VALUES
('Want-Ad', 'want', 'W', 'Buying / Hiring'),
('Selling', 'selling', 'S', 'For Sale / For Hire'),
('Agent', 'agent', 'A', 'Buyer-Seller / Contractor');

CREATE TABLE IF NOT EXISTS `categories` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `bizn_price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `pdcst_price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `pdcst_renew` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--ALTER TABLE `badaddb`.`categories` ADD `pdcst_renew` DECIMAL(10,2) UNSIGNED DEFAULT NULL;
--ALTER TABLE `badadlistdb`.`ads` ADD `podcast_ad` INT UNSIGNED DEFAULT 0,

INSERT INTO `categories` (`category`, `slug`, `price`, `bizn_price`) VALUES
('Supply', 'supply', '1.00', '3.00'),
('Jobs', 'jobs', '1.00', '3.00'),
('Artists', 'artist', '1.00', '3.00'),
('Coaching', 'coach', '1.00', '3.00'),
('Webstores', 'store', '1.00', '3.00'),
('Craft', 'craft', '1.00', '3.00'),
('Resale', 'resale', '1.00', '3.00'),
('Home & Auto', 'homeauto', '1.00', '3.00'),
('Getaway', 'getaway', '1.00', '3.00'),
('Causes', 'cause', '1.00', '3.00');

CREATE TABLE IF NOT EXISTS `sub_supply` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_supply` (`subcat`, `slug`) VALUES
('Specialized Equipment', 'equipment'),
('Textiles', 'textiles'),
('Shoes & Accessories', 'accessories'),
('Carbon', 'carbon'),
('Plastic', 'plastic'),
('Metal & Casting', 'casting'),
('Circuits & Chips', 'circuits'),
('Food & Farms', 'food'),
('Pharmacy & Health Care', 'pharmacy'),
('Assembly & Complex Production', 'assembly'),
('Small Parts', 'parts'),
('Hi-Tech Materials & Fabrics', 'materials'),
('Dropshipping & Logistics', 'logistics'),
('Furniture', 'furniture'),
('Interior & Exterior Surfaces', 'surface'),
('Energy Solutions', 'energy'),
('Communications & Personal Tech', 'communications'),
('Large Machines, Trucks & Tractors', 'machines'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_jobs` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_jobs` (`subcat`, `slug`) VALUES
('Copywriting & Ghostwriting', 'copywriting'),
('Project Management & Contractors', 'projects'),
('Apps Websites UI PM IT Networks Software', 'software'),
('Graphic Design Illustration & Animation', 'graphic'),
('Machines & Physical Architecture Engineering', 'architecture'),
('Electronic & Chemical Engineering', 'electrochemical'),
('Advanced Software & Simulation Engineering', 'advanced'),
('Statistics & Data Analysis', 'analysis'),
('Clothing Accessory Apparel Design', 'apparel'),
('Product Testing & Admin Work at Home', 'testing'),
('Recipe & Hospitality Planning', 'hospitality'),
('Marketing & Advertising', 'marketing'),
('Accounting, Investment & Money Services', 'accounting'),
('Strategy & Brand Identity', 'strategy'),
('Specialized Consulting', 'consulting'),
('Operations & Oversight Planning', 'operations'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_artist` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_artist` (`subcat`, `slug`) VALUES
('On-Site Sculpting & Painting', 'onsite'),
('Motivational Speaking', 'motivational'),
('Musical Performance', 'music'),
('Stage & Theater', 'stage'),
('Entertainment & Comedy', 'comedy'),
('Specialized Talent', 'talent'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_coach` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_coach` (`subcat`, `slug`) VALUES
('Second Language', 'sl'),
('Academic Subjects', 'academic'),
('Art Tutor', 'art'),
('Diet & Food', 'food'),
('Fitness & Sports', 'sports'),
('Travel & Guides', 'guides'),
('Life Coaching', 'lifecoach'),
('Brand Consulting', 'brandconsult'),
('IT Consulting', 'itconsult'),
('Web Business Consulting', 'webconsult'),
('Logistics Consulting', 'logconsult'),
('Solutions Consulting', 'solconsult'),
('Other Professional Consulting', 'proconsult'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_store` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_store` (`subcat`, `slug`) VALUES
('2nd Hand', '2ndhand'),
('Tech & AV', 'tech'),
('Indoors', 'indoors'),
('Parts & Replacement', 'parts'),
('Outdoors', 'outdoors'),
('Books, Videos, Music, etc', 'books'),
('Digital Media', 'digitalmedia'),
('Art & Inspiration', 'inspiration'),
('Stationary', 'stationary'),
('Errands & Grocery', 'grocery'),
('Niche & Gadgets', 'niche'),
('Food & Beverage', 'food'),
('Apparel & Bags', 'apparel'),
('General Services', 'service'),
('Shipping & Delivery', 'shipping'),
('Blogs, Journals & News', 'blognews'),
('Podcasts, Vlogs & Social Accounts', 'vlogsoc'),
('Online Classes', 'classes'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_craft` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_craft` (`subcat`, `slug`) VALUES
('Wood & Metal Carpentry', 'carpentry'),
('Pottery & Plaster', 'pottery'),
('Fabric Leather Clothing', 'fabric'),
('Metal Welding Casting Lathe', 'metal'),
('Sculpture & Painted Art', 'sculpture'),
('Gadgets & Inventions', 'gadgets'),
('Mini Computer Kits', 'kits'),
('Other', 'other');

--CREATE TABLE IF NOT EXISTS `sub_announce` (
--  `id` SMALLINT NOT NULL AUTO_INCREMENT,
--  `subcat` VARCHAR(50) NOT NULL,
--  `slug` VARCHAR(50) NOT NULL,
--  PRIMARY KEY (`id`),
--  UNIQUE KEY `slug` (`slug`),
--  UNIQUE KEY `subcat` (`subcat`)
--) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--INSERT INTO `sub_announce` (`subcat`, `slug`) VALUES
--('People', 'people'),
--('Business', 'business'),
--('Government', 'government'),
--('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_resale` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_resale` (`subcat`, `slug`) VALUES
('Mobile Phones', 'mobile'),
('Tablets', 'tablets'),
('Music', 'music'),
('Movies', 'movies'),
('Video Games', 'vidgames'),
('Computer Hardware', 'comp_hardware'),
('Audio-Video Equipment', 'av_equip'),
('Musical Instruments', 'mus_insturment'),
('Sports Equipment', 'sports_equip'),
('Collectable', 'collectable'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_homeauto` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_homeauto` (`subcat`, `slug`) VALUES
('Home Condo Timeshare', 'home'),
('Automobile', 'automobile'),
('Office & Warehouse', 'office'),
('Jewelry Accessory', 'accessory'),
('Furnishing', 'furnishing'),
('Recreational Vehicles', 'recreational'),
('Entertainment Center', 'entertainment'),
('Artwork', 'artwork'),
('Collectable', 'collectable'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_getaway` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_getaway` (`subcat`, `slug`) VALUES
('Religious Conference', 'relconf'),
('Special Conference', 'specialconf'),
('Hotel & Hostel', 'host'),
('Food & Drink', 'foodbev'),
('Amusement & Fairs', 'amusement'),
('Casinos', 'casino'),
('Movie Cinemas', 'cinema'),
('Theater & Stage', 'thesp'),
('Retreat & Camp', 'camp'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `sub_cause` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `subcat` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `subcat` (`subcat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_cause` (`subcat`, `slug`) VALUES
('Petitions & Cases', 'petition'),
('Charity Groups', 'charity'),
('Scholarship & Campus', 'campus'),
('Crowdfunding Startup', 'crowd'),
('Help I am being sued', 'sued'),
('Hostile takeover', 'takeover'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `tags` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(50) NOT NULL,
  `merged` ENUM('unique', 'merged') NOT NULL,
  `merged_into_id` SMALLINT DEFAULT NULL,
  `cat_id` SMALLINT DEFAULT NULL,
  `subcat_id` SMALLINT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--INSERT INTO `tags` (`tag`) VALUES ('factory');
--INSERT INTO `tags` (`tag`) VALUES ('advertising');
--INSERT INTO `tags` (`tag`) VALUES ('english');
--INSERT INTO `tags` (`tag`) VALUES ('speaker');
--INSERT INTO `tags` (`tag`) VALUES ('team');
--INSERT INTO `tags` (`tag`) VALUES ('jokes');
--INSERT INTO `tags` (`tag`) VALUES ('standup');
--INSERT INTO `tags` (`tag`) VALUES ('diet');
--INSERT INTO `tags` (`tag`) VALUES ('recipe');
--INSERT INTO `tags` (`tag`) VALUES ('advice');
--INSERT INTO `tags` (`tag`) VALUES ('lifecoach');
--INSERT INTO `tags` (`tag`) VALUES ('tech');
--INSERT INTO `tags` (`tag`) VALUES ('computer');
--INSERT INTO `tags` (`tag`) VALUES ('books');
--INSERT INTO `tags` (`tag`) VALUES ('office');
--INSERT INTO `tags` (`tag`) VALUES ('pottery');
--INSERT INTO `tags` (`tag`) VALUES ('dishware');
--INSERT INTO `tags` (`tag`) VALUES ('metalwork');
--INSERT INTO `tags` (`tag`) VALUES ('furniture');
--INSERT INTO `tags` (`tag`) VALUES ('cars');
--INSERT INTO `tags` (`tag`) VALUES ('sportscar');
--INSERT INTO `tags` (`tag`) VALUES ('workout');
--INSERT INTO `tags` (`tag`) VALUES ('bodybuilding');
--INSERT INTO `tags` (`tag`) VALUES ('freelance');

-- INSERT INTO `tags` (`tag`) VALUES ('newtag');
-- (LOWER('tag'));

CREATE TABLE IF NOT EXISTS `ads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pub_status` ENUM('pending', 'live', 'expired', 'dead') NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `ad_lang` ENUM('en', 'zh', 'fr', 'nl', 'cs', 'de', 'he', 'ar', 'es', 'pt', 'ru', 'ms', 'el', 'jp', 'ko', 'sv', 'fi', 'it', 'nv', 'hi') NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `subcat_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `tag_ids` LONGTEXT DEFAULT NULL,
  `ad_comment` LONGTEXT NOT NULL,
  `ad_nickname` VARCHAR(56) NOT NULL,
  `ad_content_hdng` LONGTEXT NOT NULL,
  `ad_content_dscr` LONGTEXT NOT NULL,
  `ad_content_info` LONGTEXT NOT NULL,
  `ad_content_pyrt` LONGTEXT NOT NULL,
  `ad_content_cntc` LONGTEXT NOT NULL,
  `ad_content_bizn` LONGTEXT DEFAULT NULL,
  `ad_biz_listing` ENUM('non','biz') NOT NULL,
  `ad_weekslong` INT UNSIGNED DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_starts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `week_view_count` BIGINT DEFAULT 0,
  `week_cat_count` BIGINT DEFAULT 0,
  `week_tag_count` BIGINT DEFAULT 0,
  `week_search_count` BIGINT DEFAULT 0,
  `epoch_wk_reset` BIGINT DEFAULT NULL,
  `chase_ad_id` BIGINT UNSIGNED DEFAULT NULL,
  `rerun_id` BIGINT UNSIGNED DEFAULT NULL,
  `rerun_how` ENUM('Original','Rerun','Modified') NOT NULL,
  `modified_yn` ENUM('Final','Modified') NOT NULL,
  `base_price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `discount` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `price_total` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `receipt_email` VARCHAR(90) NOT NULL,
  `statement_description` VARCHAR(255) DEFAULT NULL,
  `receipt_url` VARCHAR(255) DEFAULT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `paid_amount` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `payment_status` VARCHAR(50) DEFAULT NULL,
  `payment_date_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `podcast_ad` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `subcat_id` (`subcat_id`),
  KEY `user_id` (`user_id`),
  KEY `creation_date` (`date_created`),
  UNIQUE KEY `transaction_id` (`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--INSERT INTO `ads` (`user_id`, `category_id`, `subcat_id`, `role_id`, `tag_ids`, `ad_nickname`, `ad_comment`, `ad_content_hdng`, `ad_content_dscr`, `ad_content_info`, `ad_content_pyrt`, `ad_content_cntc`, `ad_weekslong`, `pub_status`, `epoch_wk_reset`, `date_created`, `date_expires`, `base_price`, `price_total`, `receipt_email`, `transaction_id`, `payment_status`, `paid_amount`) VALUES(1, 2, 1, 1, '2, 3', 'Copywriter Needed', 'SQL Script', 'Copywriter Needed', 'Weekly writings & product description', 'catalogs, blogs, articles, news, ads', '$50/hr est.', 'https://pdt.news', '152', 'live', '1564139984', '2019-07-19 19:19:43', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL -1000 DAY), '1.00', '1.00', 'admail@pdt.news', 'EXAMPLE26', 'EXAMPLE', '1.00');

CREATE TABLE IF NOT EXISTS `pod_ads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED DEFAULT NULL,
  `status` ENUM('pending', 'inreview', 'edited', 'resubmitted', 'approved', 'recorded', 'rerecord', 'live', 'expired', 'dead') NOT NULL,
  `customer_user` BIGINT UNSIGNED DEFAULT 0,
  `editor_user` BIGINT UNSIGNED DEFAULT 0,
  `voice_user` BIGINT UNSIGNED DEFAULT 0,
  `publisher_user` BIGINT UNSIGNED DEFAULT 0,
  `original_manuscript` LONGTEXT NOT NULL,
  `edited_manuscript` LONGTEXT DEFAULT '',
  `resubmitted_manuscript` LONGTEXT DEFAULT '',
  `approved_manuscript` LONGTEXT DEFAULT '',
  `length` TEXT NOT NULL DEFAULT 0,
  `duration` TINYTEXT DEFAULT 'empty',
  `rerun_pod_id` BIGINT UNSIGNED DEFAULT 0,
  `date_starts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_approved` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_recorded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_published` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_id` (`ad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `current_cycle` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `date_calculated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--INSERT INTO current_cycle (ad_id, price) VALUES ('1', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('2', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('3', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('4', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('5', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('6', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('7', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('8', '3.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('9', '3.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('10', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('11', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('12', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('13', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('14', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('15', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('16', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('17', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('18', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('19', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('20', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('21', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('22', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('23', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('24', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('25', '1.00');
--INSERT INTO current_cycle (ad_id, price) VALUES ('26', '1.00');

CREATE TABLE IF NOT EXISTS `partners` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(90) NOT NULL,
  `email_confirmed` ENUM('Unconfirmed','Confirmed') NOT NULL,
  `tc_partner_agreement` BOOLEAN NOT NULL,
  `tc_income_no_guarantee` BOOLEAN NOT NULL,
  `tc_usa_ein` BOOLEAN NOT NULL,
  `tc_paypal_ein` BOOLEAN NOT NULL,
  `tc_tax_truth` BOOLEAN NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `need_accept_new_tc` BOOLEAN NOT NULL DEFAULT false,
  `need_see_new_categories` BOOLEAN NOT NULL DEFAULT false,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `deletedpartners` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `partnerid` BIGINT UNSIGNED NOT NULL,
  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partnerid` (`partnerid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `partnersites_tallied` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `site_id` BIGINT UNSIGNED NOT NULL,
  `listed_badad_count` BIGINT UNSIGNED DEFAULT 0,
  `listed_ad_count` BIGINT UNSIGNED DEFAULT 0,
  `clicked_badad_count` BIGINT UNSIGNED DEFAULT 0,
  `clicked_listed_count` BIGINT UNSIGNED DEFAULT 0,
  `tallying_user_id` INT UNSIGNED NOT NULL,
  `date_calculated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `feeds_tallied` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `feed_requested_count` BIGINT UNSIGNED DEFAULT 0,
  `ad_download_count` BIGINT UNSIGNED DEFAULT 0,
  `ad_click_count` BIGINT UNSIGNED DEFAULT 0,
  `tallying_user_id` INT UNSIGNED NOT NULL,
  `date_calculated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tallied_cycles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `tallying_user_id` BIGINT UNSIGNED NOT NULL,
  `date_calculated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- For for site-work
CREATE TABLE IF NOT EXISTS `tallykey` (
  `email` VARCHAR(90) NOT NULL,
  `calckey` VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- For site-work
CREATE TABLE IF NOT EXISTS `writerkey` (
  `email` VARCHAR(90) NOT NULL,
  `writekey` VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- INSERT INTO `orders` (`tag_ids`) VALUES ('[1,2,3]');

CREATE TABLE IF NOT EXISTS `referrallinks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `reflink` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `referrals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `referring_id` INT UNSIGNED NOT NULL,
  `referred_id` INT UNSIGNED NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `referred_id` (`referred_id`),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `credits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `creditcount` INT UNSIGNED NOT NULL,
  UNIQUE KEY `userid` (`userid`),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `temppasswords` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `loginonce` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'used', 'dead') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `logincode` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'used', 'dead') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmchange` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `confirmkey` VARCHAR(255) DEFAULT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confirmemail` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(90) NOT NULL,
  `temppass` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'dead') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_dead` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `emailsubscriptions` (
  `email` VARCHAR(90) NOT NULL,
  `delkey` VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `emailwrongunsubscribe` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(90) NOT NULL,
  `delkey` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'used', 'dead') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_done` TIMESTAMP DEFAULT '2020-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rememberme` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `key_a` VARCHAR(255) DEFAULT NULL,
  `key_b` VARCHAR(255) DEFAULT NULL,
  `useable` ENUM('live', 'dead', 'expired') NOT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expires` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `weeklyavgview` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `epoch` INT UNSIGNED NOT NULL,
  `avgviews` INT UNSIGNED NOT NULL,
  `sumviews` INT UNSIGNED NOT NULL,
  `sumquery` INT UNSIGNED NOT NULL,
  `date_entry` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `weeklyavglisten` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `epoch` INT UNSIGNED NOT NULL,
  `sumlisten` INT UNSIGNED NOT NULL,
  `date_entry` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('member','editor','voice','editorvoice','publisher','supervisor','admin') NOT NULL,
  `username` VARCHAR(32) NOT NULL,
  `email` VARCHAR(90) NOT NULL,
  `confirmed_email` VARCHAR(90) DEFAULT 'Unconfirmed',
  `status` ENUM('ok','emailwrong') NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `project` VARCHAR(80) DEFAULT NULL,
  `tfa` ENUM('none', 'email_link', 'email_code', 'sms_code', 'google_auth', 'app_tap') NOT NULL,
  `join_rank` INT UNSIGNED NOT NULL DEFAULT 0,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tc_partner` BOOLEAN DEFAULT NULL,
  `tc_signup` BOOLEAN NOT NULL,
  `tc_honesty` BOOLEAN NOT NULL,
  `tc_tags` BOOLEAN NOT NULL,
  `tc_spam` BOOLEAN NOT NULL,
  `tc_tm` BOOLEAN NOT NULL,
  `tc_norefund` BOOLEAN NOT NULL,
  `tc_dualauth` BOOLEAN NOT NULL DEFAULT 0,
  `tc_beta` BOOLEAN NOT NULL,
  `pass` VARCHAR(255) DEFAULT NULL,
  `app_tap_key` VARCHAR(255) DEFAULT NULL,
  `sec_key` VARCHAR(64) DEFAULT 0,
  `need_accept_new_tc` BOOLEAN NOT NULL DEFAULT false,
  `need_see_security_notice` BOOLEAN NOT NULL DEFAULT false,
  `need_lookover_account_info` BOOLEAN NOT NULL DEFAULT false,
  `user_lang` ENUM ('en', 'zh', 'fr', 'nl', 'cs', 'de', 'he', 'ar', 'es', 'pt', 'ru', 'ms', 'el', 'jp', 'ko', 'sv', 'fi', 'it', 'nv', 'hi') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- 17 -- INSERT INTO users (type, username, email, confirmed_email, name, tc_partner, tc_signup, tc_honesty, tc_tags, tc_spam, tc_tm, tc_norefund, tc_dualauth, tc_beta)
--VALUES ('member', 'editoruahewrfiuwhefi287', 'editor@badad.one', 'editor@badad.one', 'Editor', 0,0,0,0,0,0,0,0,0);
-- 18 -- INSERT INTO users (type, username, email, confirmed_email, name, tc_partner, tc_signup, tc_honesty, tc_tags, tc_spam, tc_tm, tc_norefund, tc_dualauth, tc_beta)
--VALUES ('member', 'voicefja984fju94e8fj4fd', 'voice@badad.one', 'voice@badad.one', 'Voice', 0,0,0,0,0,0,0,0,0);
-- 19 -- INSERT INTO users (type, username, email, confirmed_email, name, tc_partner, tc_signup, tc_honesty, tc_tags, tc_spam, tc_tm, tc_norefund, tc_dualauth, tc_beta)
--VALUES ('member', 'publisherc4c849ja483899', 'publisher@badad.one', 'publisher@badad.one', 'Publisher', 0,0,0,0,0,0,0,0,0);

CREATE TABLE IF NOT EXISTS `freekeys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `reg_key` VARCHAR(255) DEFAULT NULL,
  `purchase_key` VARCHAR(255) DEFAULT NULL,
  `purchase_useable` ENUM('live', 'dead') NOT NULL,
  `date_reg_expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- UPDATE users SET type='admin' WHERE username='admin';

CREATE TABLE IF NOT EXISTS `deletedusers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` BIGINT UNSIGNED NOT NULL,
  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Test table
CREATE TABLE IF NOT EXISTS `test_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `string` TINYTEXT NOT NULL,
  `logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Emails
CREATE DATABASE badademaildb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON badademaildb.* TO 'badademaildb'@'localhost' IDENTIFIED BY 'badademaildbpassword';
FLUSH PRIVILEGES;

USE badademaildb;

CREATE TABLE IF NOT EXISTS `pantry` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` TINYTEXT NOT NULL,
  `ver` INT UNSIGNED NOT NULL,
  `subject` TINYTEXT NOT NULL,
  `body` LONGTEXT NOT NULL,
  `footer` LONGTEXT DEFAULT NULL,
  `created` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

INSERT INTO pantry (slug, ver, subject, body) VALUES ('sysemailtest', 1, 'badAd SysEmail Test', '<p>This paragraph is the system introduction in the canned email.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_new', 1, 'badAd New Podcast Ad Manuscript Submission', '<p>A new podcast ad needs manuscript approval.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_edited', 1, 'badAd Podcast Ad Manuscript Needs Changes', '<p>Your podcast ad manuscript needs changes.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_resubmitted', 1, 'badAd Podcast Ad Manuscript Re-submission', '<p>A podcast ad manuscript was resubmitted and needs review.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_approved', 1, 'badAd Podcast Ad Ready for Recording', '<p>A podcast ad is ready for voice recording.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_recorded', 1, 'badAd Podcast Ad Voice Needs Approval', '<p>A podcast ad recording needs approval.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('podad_rerecord', 1, 'badAd Podcast Ad Needs Re-recorded', '<p>Your podcast ad needs recording again.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('feedback', 1, 'badAd Feedback Form', '<p>For your reference, this is a copy of the information you sent to us through our feedback form...</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('forgot_password', 1, 'Forgot Password', '<p>You requested to change your password.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('ad_changed', 1, 'Ad Changed', '<p>Just letting you know, you changed an ad.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_change', 1, 'Confirm Account Change', '<p>Important information in your account has been changed. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_email', 1, 'Confirm Email', '<p>Please confirm your account email address.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_partner_email', 1, 'Activate Parnter Account', '<p>Please confirm your PayPal email address for your Partner account.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('confirm_user_del_account', 1, 'Delete User Account', '<p>There is a request to delete your User account. Was this you?</p>', '<p>All the private information we have pertaining to your account, including your Order History and any statistics for business ads, is still available to you free of charge. If you desire to obtain this information, do so before deleting your account. Requests for any such information we may retain after an account is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('referral_gen', 1, 'Referral Link', '<p>When someone signs up and buys an ad with this link, both of you get a free 1-week ad credit!</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('register', 1, 'Registration', '<p>Thank you for registering at badAd.one. You agreed to our Terms & Conditions, which may change and you will receive an email when you do. You also agreed that all sales are final and no refunds are given under any circumstances.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_partner_change', 1, 'Partner Account Change', '<p>Something changed in your Partner account. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('partner_site_added', 1, 'Partner Site Added', '<p>Just letting you know that a new Partner Site Project was <b>added</b> to your account. If that was you, please let us know.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('partner_site_delete', 1, 'Request Partner Site Delete', '<p>We need confirmation from you. A request was made to <b>delete</b> a project from your Partner account. It will not be deleted until you confirm. You can still cancel this request.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('confirm_partner_del_account', 1, 'Delete Partner Account', '<p>There is a request to delete your Partner account. Was this you?</p>', '<p>All the private information we have pertaining to your Partner account is still available to you free of charge. If you desire to obtain this information, do so before deleting your Partner account. Requests for any such information we may retain after an account is deleted incurs a minimum fee of $1,000 USD, plus tech support time spent obtaining the data, and any such requests can only be made in person.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_partner_del_project', 1, 'Delete Partner Project', '<p>A project was permanently deleted from your Partner account. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('partner_email_change', 1, 'Change Partner Payout Email', '<p>There is a request to change your Partner payout email. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('email_link', 1, 'Login Link', '<p>Use this link to log in:</p>', '<p>The link is only active for about 40 minutes and can only be used one time.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('email_code', 1, 'Login Code', '<p>Use this code to log in:</p>', '<p>The code is only active for about 40 minutes and can only be used one time.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('newsletter', 1, 'Newsletter', '<p>Here\'s the latest, greatest...</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('vitalallusernotice', 1, 'Vital Info for You', '<p>We have vital information!</p>', '<p>Thank you for your time.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('vitalallpartnernotice', 1, 'Vital Info for You', '<p>We have vital information for you as a Partner!</p>', '<p>Thank you for your time.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('updateuserterms', 1, 'Update to our Terms & Policies', '<p>We made changes to our Terms & Conditions! Review our <a title="Terms & Conditions" href="https://badad.one/Terms.htm">Terms & Conditions</a> and <a title="Privacy" href="https://badad.one/Privacy.htm">Privacy</a> policy.</p>', '<p>Thank you for your time.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('payment_receipt', 1, 'Receipt for Your New Ad', '<p>Here is the receipt for your ad! You may want to print this for your records.</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('payment_declined', 1, 'Payment Declined', '<p>Sorry to inform you, your payment was declined, but it has been saved and is waiting for you.</p>', '<p>To finish your purchase, just click "checkout" next to the ad in your <a title="Order History" href="https://badad.one/order_history.htm">Order History</a>.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('partner_app_added', 1, 'Partner App Added', '<p>Just letting you know that a new Partner App Project was <b>added</b> to your account. If that was you, please let us know.</p>');

INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_partner_dev_del_app', 1, 'Delete Dev App', '<p>A Dev App was permanently deleted from your Developer account. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('confirm_partner_dev_change', 1, 'Dev App Change', '<p>Something changed in your Partner account\'s Developer Center. Was this you?</p>');
INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('dev_app_added', 1, 'Dev App Added', '<p>Just letting you know that a new Dev App was <b>added</b> to your account.</p>', '<p>If that was you, please let us know.</p>');
INSERT INTO pantry (slug, ver, subject, body) VALUES ('dev_app_delete', 1, 'Request Dev App Delete', '<p>We need confirmation from you. A request was made to <b>delete</b> a project from your Partner account. It will not be deleted until you confirm. You can still cancel this request.</p>');


-- INSERT INTO pantry (slug, ver, subject, body, footer) VALUES ('SLUG', 1, 'SUBJECT', 'BODY', 'FOOTER');

CREATE TABLE IF NOT EXISTS `sent_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `user_email` TEXT NOT NULL,
  `can_id` INT UNSIGNED NOT NULL,
  `can_ver` INT UNSIGNED NOT NULL,
  `sent` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sent_log_mass` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `can_slug` TINYTEXT NOT NULL,
  `can_id` INT UNSIGNED NOT NULL,
  `can_ver` INT UNSIGNED NOT NULL,
  `subject` TINYTEXT NOT NULL,
  `message` LONGTEXT DEFAULT NULL,
  `sent` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;



-- Add to badademaildb: notice tracking (NOT YET ADDED!)
-- These are for tracking important user notifications
-- Needs:
  -- System to add usernotice entries to relevant users
  -- System to send a key-link email to users
  -- Page to "accept" and/or "read" (viz usernotice.type)
  -- login_check.inc to see if users should be notified of "unread notices"

-- Log of notices the user has and has not read
CREATE TABLE IF NOT EXISTS `userread` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `notice_id` INT UNSIGNED NOT NULL,
  `key_a` VARCHAR(255) DEFAULT NULL,
  `key_b` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('unread', 'done') NOT NULL,
  `email_notice_sent` ENUM('yes', 'no') NOT NULL,
  `last_email` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_status` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Actual notices
CREATE TABLE IF NOT EXISTS `usernotice` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class` ENUM('red', 'yellow', 'green', 'blue', 'gray', 'black') NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `type` ENUM('notice', 'page_read', 'page_accept') NOT NULL,
  `for` ENUM('user', 'partner') NOT NULL,
  `banner` TINYTEXT NOT NULL,
  `subject` TINYTEXT NOT NULL,
  `message` LONGTEXT DEFAULT NULL,
  `useable` ENUM('live', 'off', 'deleted') NOT NULL,
  `date_retired` TIMESTAMP NOT NULL DEFAULT '2250-01-01 00:00:00',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- END Add to badademaildb: notice tracking

-- Aggregation/Podcasts
CREATE DATABASE badadfeeddb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON badadlistdb.* TO 'badadlistdb'@'localhost' IDENTIFIED BY 'badadlistdbpassword';
FLUSH PRIVILEGES;

USE badadfeeddb;

CREATE TABLE IF NOT EXISTS `feeds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `source` TEXT DEFAULT NULL,
  `slug` TINYTEXT DEFAULT NULL,
  `old_slug` TINYTEXT DEFAULT '',
  `status` ENUM('live', 'off', 'failed', 'empty', 'deleted') NOT NULL,
  `itunes_status` ENUM('ready', 'partial', 'custom', 'absent', 'update') NOT NULL,
  `override_feed_settings` ENUM('yes', 'no') NOT NULL,
  `global_subcat_ids` LONGTEXT DEFAULT NULL,
  `auto_add_new_cat` BOOLEAN NOT NULL DEFAULT true,
  `title` TINYTEXT DEFAULT NULL,
  `link` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `copyright` TEXT DEFAULT NULL,
  `image_url` TEXT DEFAULT NULL,
  `image_title` TEXT DEFAULT NULL,
  `image_link` TEXT DEFAULT NULL,
  `language` VARCHAR(9) NOT NULL DEFAULT 'en-us',
  `lastbuilddate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `itunes_title` TEXT DEFAULT NULL,
  `itunes_type` ENUM('episodic', 'serial') NOT NULL,
  `itunes_complete` ENUM('not', 'yes') NOT NULL,
  `itunes_image` TEXT DEFAULT NULL,
  `itunes_author` TINYTEXT DEFAULT NULL,
  `itunes_summary` TEXT DEFAULT NULL,
  `itunes_owner_name` TINYTEXT DEFAULT NULL,
  `itunes_owner_email` TINYTEXT DEFAULT NULL,
  `itunes_keywords` TEXT DEFAULT NULL,
  `itunes_explicit` ENUM('true', 'false') NOT NULL,
  `itunes_cat1` VARCHAR(255) DEFAULT NULL,
  `itunes_cat2` VARCHAR(255) DEFAULT NULL,
  `itunes_cat3` VARCHAR(255) DEFAULT NULL,
  `itunes_cat4` VARCHAR(255) DEFAULT NULL,
  `itunes_cat5` VARCHAR(255) DEFAULT NULL,
  `ba_title` TINYTEXT DEFAULT 'ba-empty',
  `ba_link` TEXT DEFAULT 'ba-empty',
  `ba_description` TINYTEXT DEFAULT 'ba-empty',
  `ba_copyright` TEXT DEFAULT 'ba-empty',
  `ba_image_url` TEXT DEFAULT 'ba-empty',
  `ba_image_title` TEXT DEFAULT 'ba-empty',
  `ba_image_link` TEXT DEFAULT 'ba-empty',
  `ba_language` VARCHAR(9) NOT NULL DEFAULT 'en-us',
  `ba_itunes_title` TEXT DEFAULT NULL,
  `ba_itunes_type` ENUM('episodic', 'serial') NOT NULL,
  `ba_itunes_complete` ENUM('not', 'yes') NOT NULL,
  `ba_itunes_image` TEXT DEFAULT 'ba-empty',
  `ba_itunes_author` TINYTEXT DEFAULT 'ba-empty',
  `ba_itunes_summary` TEXT DEFAULT 'ba-empty',
  `ba_itunes_owner_name` TINYTEXT DEFAULT 'ba-empty',
  `ba_itunes_owner_email` TINYTEXT DEFAULT 'ba-empty',
  `ba_itunes_keywords` TEXT DEFAULT 'ba-empty',
  `ba_itunes_explicit` ENUM('true', 'false') NOT NULL,
  `ba_itunes_cat1` VARCHAR(255) DEFAULT 'ba-empty',
  `ba_itunes_cat2` VARCHAR(255) DEFAULT 'ba-empty',
  `ba_itunes_cat3` VARCHAR(255) DEFAULT 'ba-empty',
  `ba_itunes_cat4` VARCHAR(255) DEFAULT 'ba-empty',
  `ba_itunes_cat5` VARCHAR(255) DEFAULT 'ba-empty',
  `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_tallied` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feed_requested_count` BIGINT UNSIGNED DEFAULT 0,
  `ad_download_count` BIGINT UNSIGNED DEFAULT 0,
  `ad_click_count` BIGINT UNSIGNED DEFAULT 0,
  `stitcher_url` LONGTEXT DEFAULT NULL,
  `spotify_url` LONGTEXT DEFAULT NULL,
  `apple_url` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--ALTER TABLE `badadfeeddb`.`feeds` ADD `ad_click_count` BIGINT UNSIGNED DEFAULT 0;
ALTER TABLE `badadfeeddb`.`feeds` ADD `stitcher_url` LONGTEXT DEFAULT NULL;
ALTER TABLE `badadfeeddb`.`feeds` ADD `spotify_url` LONGTEXT DEFAULT NULL;
ALTER TABLE `badadfeeddb`.`feeds` ADD `apple_url` LONGTEXT DEFAULT NULL;

-- PHP lastBuildDate to SQL:
-- $lastbuilddate = $item->lastBuildDate;
-- $lastbuilddate_php = strtotime($lastbuilddate);
-- $lastbuilddate_sql = date("Y-m-d H:i:s", substr($lastbuilddate_php, 0, 10));
-- XML lastBuildDate in GMT:
-- $lastBuildDate = gmdate('D, M j Y G:i:s', strtotime($lastbuilddate_sql)) . ' GMT';

-- PHP pubDate to SQL:
-- $pubdate = $item->pubDate;
-- $pubdate_php = strtotime($pubdate);
-- $pubdate_sql = date("Y-m-d H:i:s", substr($pubdate_php, 0, 10));
-- XML pubDate in GMT:
-- $pubDate = gmdate('D, M j Y G:i:s', strtotime($pubdate_sql)) . ' GMT';

CREATE TABLE IF NOT EXISTS `items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `pod_ad_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `title` TINYTEXT DEFAULT NULL,
  `description` TINYTEXT DEFAULT '',
  `link` TEXT DEFAULT '',
  `itunes_image` TEXT DEFAULT '',
  `itunes_title` TEXT DEFAULT NULL,
  `itunes_episodetype` ENUM('full', 'trailer', 'bonus') NOT NULL,
  `itunes_episode` INT UNSIGNED NOT NULL DEFAULT 0,
  `itunes_season` INT UNSIGNED NOT NULL DEFAULT 0,
  `itunes_duration` TINYTEXT DEFAULT 'empty',
  `enclosure_aud` TEXT NOT NULL DEFAULT 0,
  `enclosure_aud_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_aud_mime` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid_mime` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc_mime` TEXT NOT NULL DEFAULT 0,
  `guid` TEXT NOT NULL DEFAULT '',
  `itunes_explicit` ENUM('true', 'false') NOT NULL,
  `pubdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ba_aggregated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--ALTER TABLE `badadfeeddb`.`items` ADD `ba_aggregated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `archiveditems` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NOT NULL,
  `pod_ad_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `title` TINYTEXT DEFAULT NULL,
  `description` TINYTEXT DEFAULT '',
  `link` TEXT DEFAULT '',
  `itunes_image` TEXT DEFAULT '',
  `itunes_title` TEXT DEFAULT NULL,
  `itunes_episodetype` ENUM('full', 'trailer', 'bonus') NOT NULL,
  `itunes_episode` INT UNSIGNED NOT NULL DEFAULT 0,
  `itunes_season` INT UNSIGNED NOT NULL DEFAULT 0,
  `itunes_duration` TINYTEXT DEFAULT 'empty',
  `enclosure_aud` TEXT NOT NULL DEFAULT 0,
  `enclosure_aud_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_aud_mime` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_vid_mime` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc_length` TEXT NOT NULL DEFAULT 0,
  `enclosure_doc_mime` TEXT NOT NULL DEFAULT 0,
  `guid` TEXT NOT NULL DEFAULT '',
  `itunes_explicit` ENUM('true', 'false') NOT NULL,
  `pubdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ba_aggregated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ba_archived` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `podcastads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pod_ad_id` BIGINT UNSIGNED NOT NULL,
  `ad_id` BIGINT UNSIGNED NOT NULL,
  `ad_lang` ENUM('en', 'zh', 'fr', 'nl', 'cs', 'de', 'he', 'ar', 'es', 'pt', 'ru', 'ms', 'el', 'jp', 'ko', 'sv', 'fi', 'it', 'nv', 'hi') NOT NULL,
  `global_subcat_id` INT UNSIGNED DEFAULT 0,
  `serialno` VARCHAR(255) NOT NULL,
  `duration` TINYTEXT DEFAULT 'empty',
  `enclosure_aud_length` TEXT NOT NULL DEFAULT 0,
  `pub_status` ENUM('live', 'expired', 'dead') NOT NULL,
  `list_wk_count` BIGINT DEFAULT 0,
  `ad_download_count` BIGINT DEFAULT 0,
  `ad_click_count` BIGINT UNSIGNED DEFAULT 0,
  `epoch_wk_reset` BIGINT DEFAULT 0,
  `epoch_created` BIGINT DEFAULT 0,
  `epoch_starts` BIGINT DEFAULT 0,
  `epoch_dead` BIGINT DEFAULT 0,
  `rerun_pod_ad_id` BIGINT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `episode_ad_keys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `serial_key` VARCHAR(255) NOT NULL,
  `pod_ad_id` BIGINT UNSIGNED NOT NULL,
  `orig_pod_ad_id` BIGINT UNSIGNED DEFAULT 0,
  `feed_pid` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `downloaded_ad_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_id` INT UNSIGNED NOT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `request_feed_analytics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED DEFAULT NULL,
  `time_date` TIMESTAMP NOT NULL,
  `time_epoch` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
