USE boardnest;

-- Field-agent demo data. Import schema.sql first.
-- Demo credentials:
-- 1. Regular Field Agent: agent@boardnest.lk / password
-- 2. Senior Student Field Agent: senior_agent@boardnest.lk / password
-- 3. Junior Student (Tenure < 2 yrs): student@test.com / password
-- 4. Senior Student Applicant (Tenure >= 2 yrs): senior_student@test.com / password
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Safe Schema Migration (Ensures columns exist before insert)
-- ------------------------------------------------------------
SET @c_reg = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'registered_date');
SET @s1 = IF(@c_reg = 0, 'ALTER TABLE students ADD COLUMN registered_date DATE NULL', 'SELECT 1');
PREPARE stmt1 FROM @s1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @c_sen = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'is_senior_student');
SET @s2 = IF(@c_sen = 0, 'ALTER TABLE students ADD COLUMN is_senior_student TINYINT(1) DEFAULT 0', 'SELECT 1');
PREPARE stmt2 FROM @s2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @c_fa = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'is_field_agent');
SET @s3 = IF(@c_fa = 0, 'ALTER TABLE students ADD COLUMN is_field_agent TINYINT(1) DEFAULT 0', 'SELECT 1');
PREPARE stmt3 FROM @s3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

SET @c_nf = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'field_agents' AND COLUMN_NAME = 'nic_front');
SET @s4 = IF(@c_nf = 0, 'ALTER TABLE field_agents ADD COLUMN nic_front VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt4 FROM @s4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

SET @c_nb = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'field_agents' AND COLUMN_NAME = 'nic_back');
SET @s5 = IF(@c_nb = 0, 'ALTER TABLE field_agents ADD COLUMN nic_back VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt5 FROM @s5; EXECUTE stmt5; DEALLOCATE PREPARE stmt5;

SET @c_dist = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'field_agents' AND COLUMN_NAME = 'assigned_district');
SET @s_dist = IF(@c_dist = 0, 'ALTER TABLE field_agents ADD COLUMN assigned_district VARCHAR(100) NULL AFTER mobile', 'SELECT 1');
PREPARE stmt_dist FROM @s_dist; EXECUTE stmt_dist; DEALLOCATE PREPARE stmt_dist;

ALTER TABLE field_agents MODIFY COLUMN recruit_mode ENUM('self_registered','admin_created','senior_student') DEFAULT 'self_registered';

-- ------------------------------------------------------------
-- Demo Users
-- ------------------------------------------------------------
INSERT INTO users (user_id, full_name, email, password_hash, role, status, created_at)
VALUES
    (2, 'Sobashi Hirushani', 'agent@boardnest.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'field_agent', 'active', '2026-01-10 10:00:00'),
    (3, 'Demo Landlord', 'landlord@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'active', '2026-01-10 10:00:00'),
    (4, 'Demo Junior Student', 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', '2026-05-15 10:00:00'),
    (5, 'Kavindu Senanayake (Senior Student Agent)', 'senior_agent@boardnest.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'field_agent', 'active', '2024-01-15 10:00:00'),
    (6, 'Demo Senior Applicant', 'senior_student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', '2024-02-01 10:00:00')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    status = VALUES(status);

-- ------------------------------------------------------------
-- Field Agents (Regular + Senior Student Field Agent)
-- ------------------------------------------------------------
INSERT INTO field_agents (agent_id, user_id, nic_number, nic_front, nic_back, mobile, assigned_district, assigned_city, is_active, recruit_mode)
VALUES 
    (1, 2, '199814502890', '/boardnest/public/uploads/field_agent_nic/demo_front.jpg', '/boardnest/public/uploads/field_agent_nic/demo_back.jpg', '0775471754', 'Colombo', 'Colombo (Central)', 1, 'self_registered'),
    (2, 5, '200112345678', '/boardnest/public/uploads/field_agent_nic/demo_front.jpg', '/boardnest/public/uploads/field_agent_nic/demo_back.jpg', '0779988776', 'Colombo', 'Moratuwa', 1, 'senior_student')
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    assigned_district = VALUES(assigned_district),
    assigned_city = VALUES(assigned_city),
    is_active = VALUES(is_active),
    recruit_mode = VALUES(recruit_mode),
    nic_front = VALUES(nic_front),
    nic_back = VALUES(nic_back);

INSERT INTO landlords (landlord_id, user_id, nic_number, mobile, address, subsc_tier, consent_agreed)
VALUES (1, 3, '123456789V', '0770000000', '123 Landlord Street', 'standard', 1)
ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), mobile = VALUES(mobile);

-- ------------------------------------------------------------
-- Students (Junior Student, Senior Student Agent, Senior Applicant)
-- ------------------------------------------------------------
INSERT INTO students (student_id, user_id, nic_number, mobile, university, academic_year, registered_date, is_senior_student, is_field_agent, verf_tier)
VALUES 
    -- Junior student (< 2 years): ineligible for senior field agent
    (1, 4, '987654321V', '0711111111', 'University of Colombo', '1st Year', '2026-05-15', 0, 0, 'tier2'),
    -- Senior student who IS an active Field Agent (user_id = 5)
    (2, 5, '200112345678', '0779988776', 'University of Moratuwa', '3rd Year', '2024-01-15', 1, 1, 'tier2'),
    -- Senior student (> 2 years) candidate to test register.php flow
    (3, 6, '200087654321', '0712222222', 'University of Kelaniya', '4th Year', '2024-02-01', 1, 0, 'tier2')
ON DUPLICATE KEY UPDATE 
    user_id = VALUES(user_id), 
    mobile = VALUES(mobile),
    registered_date = VALUES(registered_date),
    is_senior_student = VALUES(is_senior_student),
    is_field_agent = VALUES(is_field_agent);

INSERT INTO properties (
    property_id, landlord_id, title, address, city, structural_type,
    latitude, longitude, maps_url, facilities, status, rent_amount
)
VALUES
    (1, 1, 'Galle Road Apartment', '10 Galle Road', 'Colombo', 'Apartment', 6.92710000, 79.86120000, 'https://maps.google.com/?q=6.9271,79.8612', 'Wi-Fi, attached bathroom, kitchen', 'verified', 28000.00),
    (2, 1, 'Duplication Road House', '25 Duplication Road', 'Colombo', 'House', 6.90000000, 79.85000000, 'https://maps.google.com/?q=6.9,79.85', 'Wi-Fi, shared kitchen', 'under_verification', 22000.00),
    (3, 1, 'Havelock Hostel', '88 Havelock Road', 'Colombo', 'Hostel', 6.89000000, 79.86000000, 'https://maps.google.com/?q=6.89,79.86', 'Wi-Fi, shared bathroom', 'verified', 18000.00),
    (4, 1, 'Marine Drive Apartment', '42 Marine Drive', 'Colombo', 'Apartment', 6.88000000, 79.85500000, 'https://maps.google.com/?q=6.88,79.855', 'Wi-Fi, attached bathroom', 'pending', 32000.00)
ON DUPLICATE KEY UPDATE
    title = VALUES(title), address = VALUES(address), city = VALUES(city),
    latitude = VALUES(latitude), longitude = VALUES(longitude), status = VALUES(status);

INSERT INTO rooms (
    room_id, property_id, room_type, price, slot_capacity,
    security_deposit, bathroom_access, wifi_available, status
)
VALUES
    (1, 1, 'single', 28000.00, 1, 28000.00, 'attached', 1, 'verified'),
    (2, 2, 'single', 22000.00, 1, 22000.00, 'shared', 1, 'under_verification'),
    (3, 3, 'shared', 18000.00, 2, 18000.00, 'shared', 1, 'verified'),
    (4, 4, 'single', 32000.00, 1, 32000.00, 'attached', 1, 'pending')
ON DUPLICATE KEY UPDATE
    property_id = VALUES(property_id), price = VALUES(price), status = VALUES(status);

INSERT INTO listings (
    listing_id, landlord_id, property_id, title, description,
    address, city, monthly_rent, status
)
VALUES
    (1, 1, 1, 'Galle Road Apartment', 'Verified apartment close to public transport.', '10 Galle Road', 'Colombo', 28000.00, 'live'),
    (2, 1, 2, 'Duplication Road House', 'House currently undergoing field verification.', '25 Duplication Road', 'Colombo', 22000.00, 'verification_pending'),
    (3, 1, 3, 'Havelock Hostel', 'Shared student hostel in Colombo.', '88 Havelock Road', 'Colombo', 18000.00, 'live'),
    (4, 1, 4, 'Marine Drive Apartment', 'New listing awaiting field verification.', '42 Marine Drive', 'Colombo', 32000.00, 'pending')
ON DUPLICATE KEY UPDATE
    property_id = VALUES(property_id), title = VALUES(title), status = VALUES(status);

INSERT INTO agent_tasks (task_id, property_id, task_type, agent_id, status, completed_at)
VALUES
    (1, 1, 'verification', 1, 'completed', CURRENT_TIMESTAMP),
    (2, 2, 'verification', 1, 'in_progress', NULL),
    (3, 3, 'verification', 1, 'completed', CURRENT_TIMESTAMP),
    (4, 4, 'verification', 2, 'in_progress', NULL),
    (5, 3, 'verification', NULL, 'pending', NULL)
ON DUPLICATE KEY UPDATE
    property_id = VALUES(property_id), agent_id = VALUES(agent_id),
    status = VALUES(status), completed_at = VALUES(completed_at);

INSERT INTO complaints (
    complaint_id, listing_id, complainant_user_id, category, description, status
)
VALUES
    (1, 3, 4, 'fee_discrepancy', 'The requested deposit differs from the listing.', 'upheld'),
    (2, 2, 4, 'security_issue', 'The gate lock is broken and needs inspection.', 'under_investigation'),
    (3, 1, 4, 'maintenance_issue', 'The roof leaks during heavy rain.', 'assigned')
ON DUPLICATE KEY UPDATE
    description = VALUES(description), status = VALUES(status);

INSERT INTO complaint_investigations (
    investigation_id, complaint_id, field_agent_user_id, findings, visit_fee_charged
)
VALUES
    (1, 1, 2, 'The complaint was confirmed during the visit.', 450.00),
    (2, 2, 2, NULL, 0.00),
    (3, 3, 5, NULL, 0.00)
ON DUPLICATE KEY UPDATE
    field_agent_user_id = VALUES(field_agent_user_id),
    findings = VALUES(findings), visit_fee_charged = VALUES(visit_fee_charged);

INSERT INTO verification_reports (
    id, task_id, field_agent_user_id, structural_safety, electrical_safety,
    fire_exit, gps_match, neighborhood_safety, furnishing_match,
    bathroom_match, kitchen_food_match, wifi_match, finance_match,
    transport_details, amenities_details, safety_details,
    photo_path_1, photo_path_2, agent_comments
)
VALUES
    (1, 1, 2, 1, 1, 1, 1, 5, 1, 1, 1, 1, 1,
     'Frequent bus service', 'Supermarket and pharmacy nearby', 'Well-lit main road',
     '/boardnest/public/upload/fieldagent/test_room1.jpg',
     '/boardnest/public/upload/fieldagent/test_room2.jpg',
     'Property passed the field verification checks.'),
    (2, 3, 2, 1, 1, 1, 1, 4, 1, 1, 1, 1, 1,
     'Bus and train access', 'Food outlets and laundry nearby', 'Safe residential area',
     '/boardnest/public/upload/fieldagent/test_room1.jpg',
     '/boardnest/public/upload/fieldagent/test_room2.jpg',
     'Hostel verification completed successfully.')
ON DUPLICATE KEY UPDATE
    field_agent_user_id = VALUES(field_agent_user_id),
    agent_comments = VALUES(agent_comments), submitted_at = CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS = 1;
