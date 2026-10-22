USE `boardnest`;
-- BoardNest / landlord-only profile-photo storage.
-- Safe for the ORIGINAL group newschema or the unified 45-table schema.
-- No existing users / landlords / room / booking tables are altered.
-- Password changing needs NO SQL changes: it uses users.password_hash.


CREATE TABLE IF NOT EXISTS `landlord_profile_photos` (
    `landlord_id` INT NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`landlord_id`),
    CONSTRAINT `fk_bn_landlord_avatar_landlord`
        FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`landlord_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Landlord-only archival marker: distinguishes voluntary owner archives from
-- properties suspended by Admin. The original group properties.status is not altered.
CREATE TABLE IF NOT EXISTS `landlord_property_archives` (
  `property_id` INT NOT NULL,
  `landlord_id` INT NOT NULL,
  `archived_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`property_id`),
  CONSTRAINT `fk_bn_archive_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bn_archive_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`landlord_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
