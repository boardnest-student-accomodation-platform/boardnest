USE `boardnest`;

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
