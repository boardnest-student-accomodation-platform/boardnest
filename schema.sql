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
    status ENUM('pending','active','suspended','banned') DEFAULT 'pending',
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

-- ========================================================
-- FIELD AGENT MODULE TABLES
-- ========================================================

-- 1. Tasks assigned to Field Agents (Verification Queue)
CREATE TABLE agent_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    agent_id INT DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (agent_id) REFERENCES field_agents(agent_id) ON DELETE SET NULL
);

-- 2. The Verification Reports submitted by Field Agents after inspection
CREATE TABLE verification_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    structural_safety TINYINT(1) DEFAULT 0,
    electrical_safety TINYINT(1) DEFAULT 0,
    fire_exit TINYINT(1) DEFAULT 0,
    gps_match TINYINT(1) DEFAULT 0,
    neighborhood_safety TINYINT(1) DEFAULT 0,
    furnishing_match TINYINT(1) DEFAULT 0,
    bathroom_match TINYINT(1) DEFAULT 0,
    wifi_match TINYINT(1) DEFAULT 0,
    finance_match TINYINT(1) DEFAULT 0,
    photo_path_1 VARCHAR(255),
    photo_path_2 VARCHAR(255),
    agent_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES agent_tasks(task_id) ON DELETE CASCADE
);

-- 3. Area Reports submitted by Field Agents
CREATE TABLE area_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    report_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES field_agents(agent_id) ON DELETE CASCADE
);
