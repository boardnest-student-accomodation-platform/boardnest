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
    is_active TINYINT(1) DEFAULT 1,
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


-- Landlord's Table (added by Admin). Change if there're any missing parts
CREATE TABLE IF NOT EXISTS listings (
    listing_id INT NOT NULL AUTO_INCREMENT,
    landlord_id INT NOT NULL,

    title VARCHAR(100) NOT NULL,
    description TEXT NULL,
    address VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    monthly_rent DECIMAL(10,2) NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'rejected',
        'reverification_requested'
    ) NOT NULL DEFAULT 'pending',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (listing_id),

    CONSTRAINT fk_listings_landlord
        FOREIGN KEY (landlord_id)
        REFERENCES landlords(landlord_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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
