-- BoardNest Database Schema
-- Import this into MySQL before starting development
-- ADD YOUR MODULES' TABLES AS YOU GROW THE TABLES- REFER TO THE DECISIONS.MD for DB rules
CREATE DATABASE IF NOT EXISTS boardnest;
USE boardnest;

-- Core users table (shared by all modules)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','landlord','field_agent','admin') NOT NULL,
    status ENUM('pending','active','rejected','suspended','banned') DEFAULT 'pending', /*added rejected status*/
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student extension table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,  /* problematic bc of dif formats in dif colleges*/
    user_id INT UNIQUE NOT NULL,
    nic_number VARCHAR(20),
    mobile VARCHAR(15),
    university VARCHAR(100),
    academic_year VARCHAR(20), /* should be auto incremented*/
    verf_tier ENUM('tier1','tier2') DEFAULT 'tier1',
    verf_deadline DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
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

-- Insert a default admin account (password: admin123)
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
    city VARCHAR(100) NOT NULL DEFAULT 'Colombo',
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
    FOREIGN KEY (landlord_id) REFERENCES landlords(landlord_id) ON DELETE CASCADE
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

-- 1. Tasks assigned to Field Agents (Verification Queue)
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

-- 2. The Verification Reports submitted by Field Agents after inspection
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

-- 3. Area Reports submitted by Field Agents
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

-- Admin owned tables

-- Announcements
CREATE TABLE IF NOT EXISTS announcements (
    announcements_id INT NOT NULL AUTO_INCREMENT,
    sent_by INT NOT NULL,
    audience ENUM(
        'all',
        'student',
        'landlord',
        'field_agent'
    ) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('normal', 'urgent') NOT NULL DEFAULT 'normal',
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (announcements_id),

    CONSTRAINT fk_ann_sender
        FOREIGN KEY (sent_by)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registration Approvals
CREATE TABLE IF NOT EXISTS registration_approvals (
    registration_approvals_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    reviewed_by INT NOT NULL,
    decision ENUM('approved', 'rejected') NOT NULL,
    rejection_reason TEXT NULL,
    reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (registration_approvals_id),

    CONSTRAINT fk_ra_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ra_reviewer
        FOREIGN KEY (reviewed_by)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Listing Approvals
CREATE TABLE IF NOT EXISTS listing_decisions (
    listing_decisions_id INT NOT NULL AUTO_INCREMENT,
    listing_id INT NOT NULL,
    admin_user_id INT NOT NULL,
    decision ENUM(
        'approved',
        'rejected',
        'reverification_requested'
    ) NOT NULL,
    rejection_reason TEXT NULL,
    decided_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (listing_decisions_id),

    CONSTRAINT fk_ld_listing
        FOREIGN KEY (listing_id)
        REFERENCES listings(listing_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ld_admin
        FOREIGN KEY (admin_user_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student module additions; retain existing field-agent data contracts.
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
    status ENUM('available','partially_occupied','occupied','pending')
        NOT NULL DEFAULT 'available',
    student_id INT NULL,
    price_type ENUM('full','partial') NOT NULL DEFAULT 'full',
    move_in DATE NULL,
    UNIQUE KEY unique_room_slot (room_id, slot_no),
    CONSTRAINT fk_slot_room
        FOREIGN KEY (room_id) REFERENCES rooms(room_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_slot_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS listing_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photo_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saved_listings (
    saved_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    listing_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (student_id, listing_id),
    CONSTRAINT fk_saved_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_saved_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    student_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_review_listing
        FOREIGN KEY (listing_id) REFERENCES listings(listing_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_review_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
