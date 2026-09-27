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
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    structural_type VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    maps_link VARCHAR(255),
    facilities TEXT,
    FOREIGN KEY (landlord_id) REFERENCES landlords(landlord_id) ON DELETE CASCADE
);

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    room_type VARCHAR(50),
    capacity INT,
    status ENUM('pending', 'under_verification', 'agent_on_site', 'suspended', 'verified') DEFAULT 'pending',
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

CREATE TABLE listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    complainant_user_id INT NOT NULL,
    category ENUM('fee_discrepancy','amenity_discrepancy','maintenance_issue','security_issue','other') DEFAULT 'other',
    description TEXT,
    status ENUM('assigned', 'under_investigation', 'resolved', 'upheld', 'dismissed', 'escalated') DEFAULT 'assigned',
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (complainant_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE complaint_investigations (
    investigation_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    field_agent_user_id INT NOT NULL,
    findings TEXT,
    visit_fee_charged DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (field_agent_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

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

