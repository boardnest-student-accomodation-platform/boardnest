<?php
// Database Initialization Script for Field Agent Module
// Fully self-contained db connection, table creation, and seeding.
// Bypasses config/db.php to prevent die() if the database does not exist.

// Compatibility Polyfill for password_hash and password_verify (PHP < 5.5)
if (!function_exists('password_hash')) {
    define('PASSWORD_DEFAULT', 1);
    function password_hash($password, $algo, $options = array()) {
        // Fallback Blowfish salt compatible with PHP 5.3 crypt()
        $salt = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye';
        return crypt($password, $salt);
    }
}
if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'boardnest';

try {
    // Connect to host directly to verify/create database
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // 1. Create Core Users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('student','landlord','field_agent','admin') NOT NULL,
        status ENUM('pending','active','suspended','banned') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 2. Create Student extension table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        student_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        nic_number VARCHAR(20),
        mobile VARCHAR(15),
        university VARCHAR(100),
        academic_year VARCHAR(20),
        verf_tier ENUM('tier1','tier2') DEFAULT 'tier1',
        verf_deadline DATE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 3. Create Landlord extension table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS landlords (
        landlord_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        nic_number VARCHAR(20),
        mobile VARCHAR(15),
        address TEXT,
        subsc_tier ENUM('standard','pro') DEFAULT 'standard',
        subsc_expires DATE,
        consent_agreed TINYINT(1) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 4. Create Field Agent extension table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS field_agents (
        agent_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        nic_number VARCHAR(20),
        mobile VARCHAR(15),
        assigned_city VARCHAR(100),
        is_active TINYINT(1) DEFAULT 1,
        recruit_mode ENUM('self_registered','admin_created') DEFAULT 'self_registered',
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 5. Create Admin table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 6. Create Properties table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
        property_id INT AUTO_INCREMENT PRIMARY KEY,
        landlord_id INT NOT NULL,
        city VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        structural_type VARCHAR(50) NOT NULL,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        maps_link VARCHAR(255),
        facilities TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 7. Create Rooms table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
        room_id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        room_type ENUM('single', 'shared') NOT NULL,
        slot_capacity INT DEFAULT 1,
        partial_occupancy TINYINT(1) DEFAULT 0,
        price DECIMAL(10, 2) NOT NULL,
        security_deposit DECIMAL(10, 2) NOT NULL,
        square_footage INT,
        furnishing VARCHAR(100),
        bathroom_access ENUM('attached', 'shared') NOT NULL,
        wifi_available TINYINT(1) DEFAULT 0,
        house_rules TEXT,
        gender_preference ENUM('male', 'female', 'mixed') DEFAULT 'mixed',
        curfew_policies TEXT,
        status ENUM('pending', 'under_verification', 'agent_on_site', 'awaiting_admin', 'live', 'rejected', 'suspended') DEFAULT 'pending',
        FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 8. Create Agent Tasks table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS agent_tasks (
        task_id INT AUTO_INCREMENT PRIMARY KEY,
        agent_id INT NULL,
        property_id INT NOT NULL,
        task_type ENUM('verification', 'complaint') DEFAULT 'verification',
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 9. Create Verification Reports table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS verification_reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        structural_safety TINYINT(1) DEFAULT 0,
        electrical_safety TINYINT(1) DEFAULT 0,
        fire_exit TINYINT(1) DEFAULT 0,
        gps_match TINYINT(1) DEFAULT 0,
        neighborhood_safety INT,
        furnishing_match TINYINT(1) DEFAULT 0,
        bathroom_match TINYINT(1) DEFAULT 0,
        wifi_match TINYINT(1) DEFAULT 0,
        finance_match TINYINT(1) DEFAULT 0,
        photo_path_1 VARCHAR(255) NOT NULL,
        photo_path_2 VARCHAR(255) NOT NULL,
        agent_comments TEXT,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES agent_tasks(task_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 10. Create Complaints table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
        complaint_id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        property_id INT NOT NULL,
        description TEXT NOT NULL,
        category ENUM('amenity_discrepancy', 'fee_discrepancy', 'safety', 'other') NOT NULL,
        status ENUM('new', 'under_moderation', 'assigned', 'investigating', 'resolved', 'dismissed') DEFAULT 'new',
        assigned_agent_id INT NULL,
        findings TEXT,
        recommendation ENUM('dismiss', 'uphold', 'escalate') NULL,
        visit_fee_charged DECIMAL(10, 2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 11. Create Area Reports table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS area_reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        agent_id INT NOT NULL,
        city VARCHAR(100) NOT NULL,
        transport_details TEXT,
        amenities_details TEXT,
        safety_details TEXT,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
    ) ENGINE=InnoDB;");

    // 12. Seed Default Admin Account if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(array('admin@boardnest.lk'));
    $adminUser = $stmt->fetch();
    $pHashAdmin = password_hash('Admin@BoardNest#99!', PASSWORD_DEFAULT);

    if (!$adminUser) {
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(array('Admin', 'admin@boardnest.lk', $pHashAdmin, 'admin', 'active'));
        $adminUserId = $pdo->lastInsertId();

        $stmtAdmin = $pdo->prepare("INSERT INTO admin (user_id) VALUES (?)");
        $stmtAdmin->execute(array($adminUserId));
    }

    // 13. Seed Test Field Agent User if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(array('agent@boardnest.lk'));
    $agentUser = $stmt->fetch();
    $pHashAgent = password_hash('Agent@BoardNest#99!', PASSWORD_DEFAULT);

    if (!$agentUser) {
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(array('Field Agent One', 'agent@boardnest.lk', $pHashAgent, 'field_agent', 'active'));
        $newUserId = $pdo->lastInsertId();

        $stmtFA = $pdo->prepare("INSERT INTO field_agents (user_id, nic_number, mobile, assigned_city, is_active, recruit_mode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtFA->execute(array($newUserId, '199812345678', '0771234567', 'Colombo', 1, 'self_registered'));
    } else {
        $stmtFA = $pdo->prepare("SELECT agent_id FROM field_agents WHERE user_id = ?");
        $stmtFA->execute(array($agentUser['user_id']));
        if (!$stmtFA->fetch()) {
            $stmtFAInsert = $pdo->prepare("INSERT INTO field_agents (user_id, nic_number, mobile, assigned_city, is_active, recruit_mode) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtFAInsert->execute(array($agentUser['user_id'], '199812345678', '0771234567', 'Colombo', 1, 'self_registered'));
        }
    }

    // 14. Seed Test Landlord User if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(array('landlord@boardnest.lk'));
    $landlordUser = $stmt->fetch();
    $pHashLandlord = password_hash('Landlord@BoardNest#99!', PASSWORD_DEFAULT);
    $landlordId = null;

    if (!$landlordUser) {
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(array('Gunapala Silva', 'landlord@boardnest.lk', $pHashLandlord, 'landlord', 'active'));
        $newUserId = $pdo->lastInsertId();

        $stmtLL = $pdo->prepare("INSERT INTO landlords (user_id, nic_number, mobile, address, subsc_tier, consent_agreed) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtLL->execute(array($newUserId, '197512345678', '0717654321', '123 Main Road, Colombo', 'pro', 1));
        $landlordId = $pdo->lastInsertId();
    } else {
        $stmtLL = $pdo->prepare("SELECT landlord_id FROM landlords WHERE user_id = ?");
        $stmtLL->execute(array($landlordUser['user_id']));
        $resLL = $stmtLL->fetch();
        $landlordId = $resLL ? $resLL['landlord_id'] : null;
    }

    // 15. Seed Test Student User if not exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(array('student@boardnest.lk'));
    $studentUser = $stmt->fetch();
    $pHashStudent = password_hash('Student@BoardNest#99!', PASSWORD_DEFAULT);
    $studentId = null;

    if (!$studentUser) {
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(array('Nimal Perera', 'student@boardnest.lk', $pHashStudent, 'student', 'active'));
        $newUserId = $pdo->lastInsertId();

        $stmtST = $pdo->prepare("INSERT INTO students (user_id, nic_number, mobile, university, academic_year, verf_tier) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtST->execute(array($newUserId, '200112345678', '0751234567', 'University of Moratuwa', 'Year 2', 'tier2'));
        $studentId = $pdo->lastInsertId();
    } else {
        $stmtST = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
        $stmtST->execute(array($studentUser['user_id']));
        $resST = $stmtST->fetch();
        $studentId = $resST ? $resST['student_id'] : null;
    }

    // 16. Seed Properties & Rooms & Tasks if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties");
    $propCount = $stmt->fetchColumn();

    if ($propCount == 0 && $landlordId !== null) {
        $stmtAgent = $pdo->prepare("SELECT agent_id FROM field_agents INNER JOIN users ON field_agents.user_id = users.user_id WHERE users.email = ?");
        $stmtAgent->execute(array('agent@boardnest.lk'));
        $agentId = $stmtAgent->fetchColumn();

        // Property 1 (Colombo - Unclaimed Pending Pool)
        $stmtProp = $pdo->prepare("INSERT INTO properties (landlord_id, city, address, structural_type, latitude, longitude, maps_link, facilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtProp->execute(array(
            $landlordId,
            'Colombo',
            '45/A, Galle Road, Katubedda, Moratuwa',
            'Annex',
            6.797284,
            79.901768,
            'https://maps.google.com/?q=6.797284,79.901768',
            'Water, Electricity, Parking'
        ));
        $prop1Id = $pdo->lastInsertId();

        $stmtRoom = $pdo->prepare("INSERT INTO rooms (property_id, room_type, slot_capacity, price, security_deposit, square_footage, furnishing, bathroom_access, wifi_available, house_rules, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRoom->execute(array(
            $prop1Id,
            'single',
            1,
            18000.00,
            36000.00,
            180,
            'Bed, Cupboard, Table',
            'attached',
            1,
            'No smoking, curfew 10 PM',
            'pending'
        ));

        // Task 1: Unclaimed Pending Task
        $stmtTask = $pdo->prepare("INSERT INTO agent_tasks (agent_id, property_id, task_type, status) VALUES (NULL, ?, 'verification', 'pending')");
        $stmtTask->execute(array($prop1Id));

        // Property 2 (Colombo - Claimed, In-Progress)
        $stmtProp->execute(array(
            $landlordId,
            'Colombo',
            '12, University Lane, Katubedda',
            'Apartment',
            6.795058,
            79.900742,
            'https://maps.google.com/?q=6.795058,79.900742',
            'AC, Gym access, Wi-Fi'
        ));
        $prop2Id = $pdo->lastInsertId();

        $stmtRoom->execute(array(
            $prop2Id,
            'shared',
            2,
            10000.00,
            20000.00,
            240,
            '2 Beds, 2 Wardrobes, 2 Desks',
            'shared',
            1,
            'Mixed gender allowed, visitors till 8 PM',
            'under_verification'
        ));

        // Task 2: Claimed In-Progress Task
        $stmtTask = $pdo->prepare("INSERT INTO agent_tasks (agent_id, property_id, task_type, status) VALUES (?, ?, 'verification', 'in_progress')");
        $stmtTask->execute(array($agentId, $prop2Id));

        // Property 3 (Completed History Task)
        $stmtProp->execute(array(
            $landlordId,
            'Colombo',
            '90, De Soysa Road, Moratuwa',
            'House',
            6.791245,
            79.897451,
            'https://maps.google.com/?q=6.791245,79.897451',
            'Garden, Security Gate'
        ));
        $prop3Id = $pdo->lastInsertId();

        $stmtRoom->execute(array(
            $prop3Id,
            'single',
            1,
            12000.00,
            24000.00,
            150,
            'Bed, Fan',
            'shared',
            0,
            'No guests overnight',
            'live'
        ));

        // Task 3: Completed Verification Task
        $stmtTask = $pdo->prepare("INSERT INTO agent_tasks (agent_id, property_id, task_type, status, completed_at) VALUES (?, ?, 'verification', 'completed', CURRENT_TIMESTAMP)");
        $stmtTask->execute(array($agentId, $prop3Id));
        $task3Id = $pdo->lastInsertId();

        // Seed a verification report for Property 3
        $stmtRep = $pdo->prepare("INSERT INTO verification_reports (task_id, structural_safety, electrical_safety, fire_exit, gps_match, neighborhood_safety, furnishing_match, bathroom_match, wifi_match, finance_match, photo_path_1, photo_path_2, agent_comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRep->execute(array(
            $task3Id, 1, 1, 1, 1, 4, 1, 1, 1, 1,
            '/boardnest/public/uploads/test_room1.jpg',
            '/boardnest/public/uploads/test_room2.jpg',
            'The property matches the description perfectly. Structural safety is up to standard.'
        ));

        // Property 4 (Active Listing for Complaint Investigation)
        $stmtProp->execute(array(
            $landlordId,
            'Colombo',
            '25, Station Road, Moratuwa',
            'Annex',
            6.787654,
            79.887654,
            'https://maps.google.com/?q=6.787654,79.887654',
            'Water, Electricity'
        ));
        $prop4Id = $pdo->lastInsertId();

        $stmtRoom->execute(array(
            $prop4Id,
            'single',
            1,
            15000.00,
            30000.00,
            160,
            'Bed, Table',
            'attached',
            1,
            'No pets, curfew 10:30 PM',
            'live'
        ));

        // Seed a complaint for Property 4 assigned to our agent
        $stmtComp = $pdo->prepare("INSERT INTO complaints (student_id, property_id, description, category, status, assigned_agent_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtComp->execute(array(
            $studentId,
            $prop4Id,
            'The Wi-Fi speed is extremely slow and unstable, contrary to what was promised. Also, the landlord is demanding an extra 2000 LKR for electricity which was not listed.',
            'amenity_discrepancy',
            'investigating',
            $agentId
        ));
    }

    if (php_sapi_name() === 'cli') {
        echo "Database initialized successfully.\n";
    }
} catch (Exception $e) {
    if (php_sapi_name() === 'cli') {
        echo "Initialization failed: " . $e->getMessage() . "\n";
    }
}
?>
