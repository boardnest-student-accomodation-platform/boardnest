-- BoardNest / landlord-only profile-photo storage.
-- Safe for the ORIGINAL group newschema or the unified 45-table schema.
-- No existing users / landlords / room / booking tables are altered.
-- Password changing needs NO SQL changes: it uses users.password_hash.
USE `boardnest`;

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
