-- BoardNest Database Schema
-- Modified to include missing EER entities

CREATE DATABASE IF NOT EXISTS boardnest;
USE boardnest;

-- Core users table (shared by all modules)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','landlord','field_agent','admin') NOT NULL,
    status ENUM('pending','active','rejected','suspended','banned') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================================
-- NEW: CITIES & UNIVERSITIES (Reference Tables)
-- ========================================================
CREATE TABLE cities (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Sri Lanka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE area_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    safety_stats TEXT,
    transport TEXT,
    amenity_logs TEXT,
    FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE universities (
    uni_id INT AUTO_INCREMENT PRIMARY KEY,
    uni_name VARCHAR(255) NOT NULL,
    province VARCHAR(100),
    city_id INT,
    FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- EXTENSION TABLES
-- ========================================================

-- Student extension table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    nic_number VARCHAR(20),
    mobile VARCHAR(15),
    uni_id INT, -- Replaced university VARCHAR with FK
    academic_year VARCHAR(20),
    verf_tier ENUM('tier1','tier2') DEFAULT 'tier1',
    verf_deadline DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (uni_id) REFERENCES universities(uni_id) ON DELETE SET NULL
);

-- Landlord extension table
CREATE TABLE landlords (
    landlord_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    nic_number VARCHAR(20),
    mobile VARCHAR(15),
    address TEXT,
    subsc_tier ENUM('standard','pro') DEFAULT 'standard',
    subsc_expires DATE,
    consent_agreed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Field Agent extension table
CREATE TABLE field_agents (
    agent_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    nic_number VARCHAR(20),
    mobile VARCHAR(15),
    assigned_city VARCHAR(100),
    is_active TINYINT(1) DEFAULT 0,
    recruit_mode ENUM('self_registered','admin_created') DEFAULT 'self_registered',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert a default admin account
INSERT INTO users (full_name, email, password_hash, role, status)
VALUES ('Admin', 'admin@boardnest.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

INSERT INTO admin (user_id) VALUES (LAST_INSERT_ID());


-- ========================================================
-- PROPERTY AND LISTING TABLES
-- ========================================================

CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT NOT NULL,
    title VARCHAR(100) NULL,
    city_id INT NULL,
    address TEXT NOT NULL,
    structural_type VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    maps_url VARCHAR(500),
    facilities TEXT,
    shared_facilities TEXT NULL,
    description TEXT,
    status ENUM('available', 'pending', 'under_verification', 'agent_on_site', 'awaiting_admin', 'verified', 'suspended') DEFAULT 'pending',
    rent_amount DECIMAL(10,2) NULL,
    images TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES landlords(landlord_id) ON DELETE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    room_type VARCHAR(50),
    listing_id INT NULL,
    slot_cap TINYINT NOT NULL DEFAULT 1,
    partial_occupancy TINYINT(1) NOT NULL DEFAULT 0,
    deposit DECIMAL(10,2) NULL,
    sq_footage INT NULL,
    furnishing ENUM('furnished','semi_furnished','unfurnished') NULL,
    bathroom_type ENUM('attached','shared') NULL,
    wifi TINYINT(1) NOT NULL DEFAULT 0,
    house_rules TEXT NULL,
    gender_pref ENUM('male','female','any') NOT NULL DEFAULT 'any',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_listing_room (listing_id),
    price DECIMAL(10,2) NULL,
    slot_capacity INT NOT NULL DEFAULT 1,
    security_deposit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    bathroom_access ENUM('attached', 'shared') NOT NULL DEFAULT 'shared',
    wifi_available TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending', 'under_verification', 'agent_on_site', 'awaiting_admin', 'suspended', 'verified') DEFAULT 'pending',
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT NOT NULL,
    property_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NULL,
    address VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    monthly_rent DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'verification_pending', 'awaiting_approval', 'live', 'active', 'inactive', 'rejected', 'suspended') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_listings_landlord FOREIGN KEY (landlord_id) REFERENCES landlords(landlord_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_listings_property FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    complainant_user_id INT NOT NULL,
    landlord_user_id INT NULL,
    unverified_stay TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    category ENUM('fee_discrepancy','amenity_discrepancy','maintenance_issue','security_issue','safety','false_advertising','landlord_misconduct','other') DEFAULT 'other',
    description TEXT,
    status ENUM('new', 'under_moderation', 'assigned', 'under_investigation', 'resolved', 'upheld', 'dismissed', 'escalated') DEFAULT 'assigned',
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (complainant_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE complaint_investigations (
    investigation_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    field_agent_user_id INT NOT NULL,
    admin_notes TEXT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    findings TEXT,
    visit_fee_charged DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (field_agent_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- FIELD AGENT MODULE TABLES
-- ========================================================
CREATE TABLE agent_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    task_type ENUM('verification', 'complaint') DEFAULT 'verification',
    agent_id INT DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES field_agents(agent_id) ON DELETE SET NULL
);

CREATE TABLE verification_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNIQUE NOT NULL,
    field_agent_user_id INT NOT NULL,
    structural_safety TINYINT(1) DEFAULT 0,
    electrical_safety TINYINT(1) DEFAULT 0,
    fire_exit TINYINT(1) DEFAULT 0,
    gps_match TINYINT(1) DEFAULT 0,
    neighborhood_safety TINYINT UNSIGNED CHECK (neighborhood_safety BETWEEN 1 AND 5),
    furnishing_match TINYINT(1) DEFAULT 0,
    bathroom_match TINYINT(1) DEFAULT 0,
    kitchen_food_match TINYINT(1) DEFAULT 0,
    wifi_match TINYINT(1) DEFAULT 0,
    finance_match TINYINT(1) DEFAULT 0,
    transport_details TEXT,
    amenities_details TEXT,
    safety_details TEXT,
    discrepancy_notes TEXT,
    photo_path_1 VARCHAR(255),
    photo_path_2 VARCHAR(255),
    photo_path_3 VARCHAR(255),
    agent_comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES agent_tasks(task_id) ON DELETE CASCADE,
    FOREIGN KEY (field_agent_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE area_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    transport_details TEXT,
    amenities_details TEXT,
    safety_details TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES field_agents(agent_id) ON DELETE CASCADE
);

-- ========================================================
-- ADMIN TABLES
-- ========================================================
CREATE TABLE IF NOT EXISTS announcements (
    announcements_id INT NOT NULL AUTO_INCREMENT,
    sent_by INT NOT NULL,
    audience ENUM('all', 'student', 'landlord', 'field_agent') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('normal', 'urgent') NOT NULL DEFAULT 'normal',
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (announcements_id),
    CONSTRAINT fk_ann_sender FOREIGN KEY (sent_by) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS registration_approvals (
    registration_approvals_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    reviewed_by INT NOT NULL,
    decision ENUM('approved', 'rejected') NOT NULL,
    rejection_reason TEXT NULL,
    reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (registration_approvals_id),
    CONSTRAINT fk_ra_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ra_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS listing_decisions (
    listing_decisions_id INT NOT NULL AUTO_INCREMENT,
    listing_id INT NOT NULL,
    admin_user_id INT NOT NULL,
    decision ENUM('approved', 'rejected', 'reverification_requested') NOT NULL,
    rejection_reason TEXT NULL,
    decided_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (listing_decisions_id),
    CONSTRAINT fk_ld_listing FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ld_admin FOREIGN KEY (admin_user_id) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- STUDENT MODULE ADDITIONS & NEW BOOKINGS TABLES
-- ========================================================
ALTER TABLE rooms ADD CONSTRAINT fk_room_listing
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('booking_accepted','booking_rejected','complaint_update','announcement','account_update') NOT NULL,
    message VARCHAR(500) NOT NULL,
    link_url VARCHAR(500) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_read (user_id, is_read),
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS room_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    slot_no TINYINT NOT NULL,
    status ENUM('available','partially_occupied','occupied','pending') NOT NULL DEFAULT 'available',
    student_id INT NULL,
    price_type ENUM('full','partial') NOT NULL DEFAULT 'full',
    move_in DATE NULL,
    UNIQUE KEY unique_room_slot (room_id, slot_no),
    CONSTRAINT fk_slot_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_slot_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NEW BOOKINGS TABLE
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    slot_id INT NOT NULL,
    bk_status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    bk_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES room_slots(slot_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NEW PAYMENTS TABLE
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    pay_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    pay_type ENUM('card', 'bank_transfer', 'cash') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NEW GROUP BOOKINGS TABLE
CREATE TABLE group_bookings (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    lead_student_id INT NOT NULL,
    grp_status ENUM('pending', 'active', 'cancelled') DEFAULT 'pending',
    member_count INT NOT NULL DEFAULT 1,
    FOREIGN KEY (lead_student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS listing_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photo_listing FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saved_listings (
    saved_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    listing_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (student_id, listing_id),
    CONSTRAINT fk_saved_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_saved_listing FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    student_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_review_listing FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_review_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========== TABLES ONLY, NO TRIGGERS OR VIEWS ==========
-- ============================================================================
-- BoardNest / Landlord additional tables ONLY
-- Based on the exact user-provided newschema.sql dated 2026-10-18.
--
-- ADDITIVE ONLY: no changes to existing tables, columns, foreign keys,
-- indexes, ENUMs or records. No ALTER, DROP, UPDATE, views or triggers.
-- Safe intent: preserve the team's original new schema unchanged.
--
-- IMPORTANT: this does NOT make the previous Landlord PHP implementation
-- compatible without adaptation. Existing bookings.bk_status, rooms.status,
-- room_slots.status, announcements columns and other contracts are unchanged.
-- The application must enforce authorization, 15-minute edits, annual rent
-- rules and synchronized booking/slot status using transactions.
-- This SQL does not implement those behavioral rules on its own.
--
-- Apply only AFTER newschema.sql has been imported successfully.
-- If an identically named table already exists with an incompatible layout,
-- CREATE TABLE IF NOT EXISTS does not repair it; stop and inspect first.
-- ============================================================================

USE `boardnest`;

-- 1. Landlord identity verification and multi-stage registration metadata.
-- Canonical account status still lives in users.status.
CREATE TABLE IF NOT EXISTS `landlord_registration_details` (
    `landlord_id` INT NOT NULL,
    `identity_status` ENUM('not_submitted','submitted','approved','rejected') NOT NULL DEFAULT 'not_submitted',
    `registration_stage` ENUM('account','identity','property_room','submitted','completed') NOT NULL DEFAULT 'account',
    `registration_deadline` DATETIME NULL,
    `warning_7d_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `warning_1d_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `nic_document_path` VARCHAR(500) NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`landlord_id`),
    CONSTRAINT `fk_bn_lreg_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Additional property-verification metadata; keep properties.status as
-- the existing authoritative property/listing workflow status.
CREATE TABLE IF NOT EXISTS `property_landlord_details` (
    `property_id` INT NOT NULL,
    `verification_submitted_at` DATETIME NULL,
    `last_reviewed_at` DATETIME NULL,
    `last_reviewed_by` INT NULL,
    `verification_notes` TEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`property_id`),
    CONSTRAINT `fk_bn_pdetail_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_pdetail_reviewer` FOREIGN KEY (`last_reviewed_by`)
        REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Landlord room labels and rent-lock metadata, without duplicating existing
-- rooms.price, slot_capacity, deposit, furnishing or gender_pref columns.
CREATE TABLE IF NOT EXISTS `room_landlord_details` (
    `room_id` INT NOT NULL,
    `room_label` VARCHAR(100) NULL,
    `rent_last_changed_at` DATETIME NULL,
    `rent_locked_until` DATETIME NULL,
    `verification_submitted_at` DATETIME NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`room_id`),
    CONSTRAINT `fk_bn_rdetail_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
    CONSTRAINT `chk_bn_rent_lock_dates` CHECK (
        `rent_last_changed_at` IS NULL OR `rent_locked_until` IS NULL
        OR `rent_locked_until` >= `rent_last_changed_at`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Current reservations only. UNIQUE(student_id) and UNIQUE(slot_id)
-- prevent multiple current reservation rows IF the booking flow uses this
-- table. Remove rows after booking cancellation/rejection/completion.
-- The original room_slots.status ENUM is NOT changed.
CREATE TABLE IF NOT EXISTS `slot_reservations` (
    `reservation_id` INT NOT NULL AUTO_INCREMENT,
    `booking_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `slot_id` INT NOT NULL,
    `reservation_status` ENUM('reserved','occupied') NOT NULL DEFAULT 'reserved',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`reservation_id`),
    UNIQUE KEY `uq_bn_res_booking` (`booking_id`),
    UNIQUE KEY `uq_bn_res_student` (`student_id`),
    UNIQUE KEY `uq_bn_res_slot` (`slot_id`),
    CONSTRAINT `fk_bn_res_booking` FOREIGN KEY (`booking_id`)
        REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_res_student` FOREIGN KEY (`student_id`)
        REFERENCES `students` (`student_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bn_res_slot` FOREIGN KEY (`slot_id`)
        REFERENCES `room_slots` (`slot_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Landlord decision, rejection reason and move-in/out dates.
-- Main bookings.bk_status stays untouched and remains canonical; the PHP
-- application must map and synchronize decision/occupancy metadata.
CREATE TABLE IF NOT EXISTS `booking_landlord_actions` (
    `booking_id` INT NOT NULL,
    `landlord_id` INT NOT NULL,
    `decision` ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    `decision_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `landlord_note` TEXT NULL,
    `occupancy_status` ENUM('not_moved_in','occupied','moved_out') NOT NULL DEFAULT 'not_moved_in',
    `move_in_date` DATE NULL,
    `move_out_date` DATE NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`booking_id`),
    KEY `idx_bn_baction_landlord` (`landlord_id`,`decision`),
    CONSTRAINT `fk_bn_baction_booking` FOREIGN KEY (`booking_id`)
        REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_baction_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE RESTRICT,
    CONSTRAINT `chk_bn_baction_move_dates` CHECK (
        `move_out_date` IS NULL OR `move_in_date` IS NULL
        OR `move_out_date` >= `move_in_date`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6-7. Photo collections for a property and its individual rooms.
CREATE TABLE IF NOT EXISTS `property_photos` (
    `photo_id` INT NOT NULL AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `caption` VARCHAR(255) NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`photo_id`),
    KEY `idx_bn_pphoto_property` (`property_id`),
    CONSTRAINT `fk_bn_pphoto_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`property_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `room_photos` (
    `photo_id` INT NOT NULL AUTO_INCREMENT,
    `room_id` INT NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `caption` VARCHAR(255) NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`photo_id`),
    KEY `idx_bn_rphoto_room` (`room_id`),
    CONSTRAINT `fk_bn_rphoto_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Nearby facilities entered by landlord and optionally verified by agent.
CREATE TABLE IF NOT EXISTS `property_facilities` (
    `facility_id` INT NOT NULL AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `facility_type` ENUM('bus_route','supermarket','grocery','pharmacy','hospital','railway_station','restaurant','other') NOT NULL DEFAULT 'other',
    `facility_name` VARCHAR(150) NOT NULL,
    `details` VARCHAR(255) NULL,
    `distance_meters` INT NULL,
    `verification_status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `verified_by_agent_id` INT NULL,
    `verified_at` DATETIME NULL,
    `agent_notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`facility_id`),
    UNIQUE KEY `uq_bn_facility_name` (`property_id`,`facility_name`),
    KEY `idx_bn_facility_status` (`property_id`,`verification_status`),
    CONSTRAINT `fk_bn_facility_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_facility_agent` FOREIGN KEY (`verified_by_agent_id`)
        REFERENCES `field_agents` (`agent_id`) ON DELETE SET NULL,
    CONSTRAINT `chk_bn_facility_distance` CHECK (`distance_meters` IS NULL OR `distance_meters` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Landlord property inspection checklist submissions.
-- This complements existing agent_tasks/verification_reports; it does not
-- replace or alter those tables.
CREATE TABLE IF NOT EXISTS `property_checklists` (
    `checklist_id` INT NOT NULL AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `landlord_id` INT NOT NULL,
    `items` LONGTEXT NOT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`checklist_id`),
    KEY `idx_bn_checklist_property` (`property_id`,`created_at`),
    CONSTRAINT `fk_bn_checklist_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_checklist_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10-11. Student/landlord messaging tied to bookings when available.
-- Authorization and booking ownership MUST be checked by PHP.
CREATE TABLE IF NOT EXISTS `conversations` (
    `conversation_id` INT NOT NULL AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `landlord_id` INT NOT NULL,
    `room_id` INT NULL,
    `booking_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_message_at` DATETIME NULL,
    PRIMARY KEY (`conversation_id`),
    UNIQUE KEY `uq_bn_conversation_booking` (`booking_id`),
    KEY `idx_bn_conversation_student` (`student_id`),
    KEY `idx_bn_conversation_landlord` (`landlord_id`),
    CONSTRAINT `fk_bn_conversation_student` FOREIGN KEY (`student_id`)
        REFERENCES `students` (`student_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_conversation_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_conversation_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`room_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bn_conversation_booking` FOREIGN KEY (`booking_id`)
        REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
    `message_id` BIGINT NOT NULL AUTO_INCREMENT,
    `conversation_id` INT NOT NULL,
    `sender_user_id` INT NOT NULL,
    `message_text` TEXT NOT NULL,
    `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_at` DATETIME NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`message_id`),
    KEY `idx_bn_messages_thread_time` (`conversation_id`,`sent_at`),
    CONSTRAINT `fk_bn_message_conversation` FOREIGN KEY (`conversation_id`)
        REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_message_sender` FOREIGN KEY (`sender_user_id`)
        REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Read tracking for EXISTING announcements(announcements_id).
-- Do not create a second announcements table or rename its columns.
CREATE TABLE IF NOT EXISTS `announcement_reads` (
    `announcement_read_id` BIGINT NOT NULL AUTO_INCREMENT,
    `announcements_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `read_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`announcement_read_id`),
    UNIQUE KEY `uq_bn_announcement_user` (`announcements_id`,`user_id`),
    CONSTRAINT `fk_bn_aread_announcement` FOREIGN KEY (`announcements_id`)
        REFERENCES `announcements` (`announcements_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_aread_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Landlord -> Student reports independent from EXISTING complaints.
-- The group's complaints table is unchanged and remains authoritative for
-- its own workflows. Do not confuse this with general listing complaints.
CREATE TABLE IF NOT EXISTS `landlord_complaints` (
    `landlord_complaint_id` INT NOT NULL AUTO_INCREMENT,
    `landlord_id` INT NOT NULL,
    `target_student_id` INT NOT NULL,
    `booking_id` INT NULL,
    `category` ENUM('property_damage','rent_dispute','conduct','safety','other') NOT NULL DEFAULT 'other',
    `description` TEXT NOT NULL,
    `evidence_path` VARCHAR(500) NULL,
    `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('submitted','under_review','assigned','resolved','dismissed') NOT NULL DEFAULT 'submitted',
    `handled_by_admin_id` INT NULL,
    `resolution_note` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `resolved_at` DATETIME NULL,
    PRIMARY KEY (`landlord_complaint_id`),
    KEY `idx_bn_lcomplaint_landlord` (`landlord_id`,`status`),
    CONSTRAINT `fk_bn_lcomplaint_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bn_lcomplaint_student` FOREIGN KEY (`target_student_id`)
        REFERENCES `students` (`student_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bn_lcomplaint_booking` FOREIGN KEY (`booking_id`)
        REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bn_lcomplaint_admin` FOREIGN KEY (`handled_by_admin_id`)
        REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Landlord Standard/Pro subscription payment history.
-- EXISTING payments is booking-specific and is not modified.
CREATE TABLE IF NOT EXISTS `subscription_payments` (
    `payment_id` INT NOT NULL AUTO_INCREMENT,
    `landlord_id` INT NOT NULL,
    `tier` ENUM('standard','pro') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payhere_order_id` VARCHAR(150) NULL,
    `payhere_payment_id` VARCHAR(150) NULL,
    `status` ENUM('pending','success','failed','cancelled') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_at` DATETIME NULL,
    PRIMARY KEY (`payment_id`),
    UNIQUE KEY `uq_bn_sub_payhere_order` (`payhere_order_id`),
    KEY `idx_bn_sub_landlord` (`landlord_id`,`status`),
    CONSTRAINT `fk_bn_sub_landlord` FOREIGN KEY (`landlord_id`)
        REFERENCES `landlords` (`landlord_id`) ON DELETE RESTRICT,
    CONSTRAINT `chk_bn_sub_amount` CHECK (`amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Append-only room rent audit history; the current price is still
-- stored in the EXISTING rooms.price column.
CREATE TABLE IF NOT EXISTS `room_rent_history` (
    `rent_history_id` BIGINT NOT NULL AUTO_INCREMENT,
    `room_id` INT NOT NULL,
    `changed_by_user_id` INT NOT NULL,
    `old_price` DECIMAL(10,2) NOT NULL,
    `new_price` DECIMAL(10,2) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`rent_history_id`),
    KEY `idx_bn_rent_history_room` (`room_id`,`changed_at`),
    CONSTRAINT `fk_bn_renth_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_renth_user` FOREIGN KEY (`changed_by_user_id`)
        REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
    CONSTRAINT `chk_bn_rent_old_price` CHECK (`old_price` >= 0),
    CONSTRAINT `chk_bn_rent_new_price` CHECK (`new_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- END: 15 new CREATE TABLE statements, no modifications to original schema.

-- BoardNest unification: three additional relationship/availability tables.
-- Original newschema.sql and earlier 15 Landlord tables remain unchanged.
USE `boardnest`;

-- Explicit maintenance blocks without changing the group's room_slots.status ENUM.
CREATE TABLE IF NOT EXISTS `room_slot_blocks` (
    `slot_id` INT NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `blocked_by_user_id` INT NOT NULL,
    `blocked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`slot_id`),
    CONSTRAINT `fk_bn_block_slot` FOREIGN KEY (`slot_id`)
        REFERENCES `room_slots` (`slot_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_block_by` FOREIGN KEY (`blocked_by_user_id`)
        REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The original group_bookings table only stored a lead_student_id and member_count.
-- This junction records *which* students belong to the group.
CREATE TABLE IF NOT EXISTS `group_booking_members` (
    `group_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`group_id`, `student_id`),
    CONSTRAINT `fk_bn_gmember_group` FOREIGN KEY (`group_id`)
        REFERENCES `group_bookings` (`group_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_gmember_student` FOREIGN KEY (`student_id`)
        REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The actual bookings belonging to each group. One booking belongs to at most one group.
CREATE TABLE IF NOT EXISTS `group_booking_items` (
    `group_id` INT NOT NULL,
    `booking_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`group_id`, `booking_id`),
    UNIQUE KEY `uq_bn_group_booking_once` (`booking_id`),
    CONSTRAINT `fk_bn_gitem_group` FOREIGN KEY (`group_id`)
        REFERENCES `group_bookings` (`group_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bn_gitem_booking` FOREIGN KEY (`booking_id`)
        REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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
