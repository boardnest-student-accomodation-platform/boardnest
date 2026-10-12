USE boardnest;

-- Field-agent demo data. Import schema.sql first.
-- Demo credentials: agent@boardnest.lk / password
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO users (user_id, full_name, email, password_hash, role, status)
VALUES
    (2, 'Sobashi Hirushani', 'agent@boardnest.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'field_agent', 'active'),
    (3, 'Demo Landlord', 'landlord@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'landlord', 'active'),
    (4, 'Demo Student', 'student@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    status = VALUES(status);

INSERT INTO field_agents (agent_id, user_id, nic_number, mobile, assigned_city, is_active, recruit_mode)
VALUES (1, 2, '199814502890', '0775471754', 'Colombo', 1, 'self_registered')
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    assigned_city = VALUES(assigned_city),
    is_active = VALUES(is_active);

INSERT INTO landlords (landlord_id, user_id, nic_number, mobile, address, subsc_tier, consent_agreed)
VALUES (1, 3, '123456789V', '0770000000', '123 Landlord Street', 'standard', 1)
ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), mobile = VALUES(mobile);

INSERT INTO students (student_id, user_id, nic_number, mobile, university, academic_year, verf_tier)
VALUES (1, 4, '987654321V', '0711111111', 'University of Colombo', '2nd Year', 'tier2')
ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), mobile = VALUES(mobile);

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
    (4, 4, 'verification', NULL, 'pending', NULL),
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
    (3, 3, 2, NULL, 0.00)
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
