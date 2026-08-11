<?php
require_once __DIR__ . '/../../config/db.php';

try {
    global $pdo;

    // 1. Ensure sample Landlord & Student exist
    $landlord = $pdo->query("SELECT landlord_id FROM landlords LIMIT 1")->fetch();
    $landlord_id = $landlord ? $landlord['landlord_id'] : 1;

    $student = $pdo->query("SELECT student_id FROM students LIMIT 1")->fetch();
    $student_id = $student ? $student['student_id'] : 1;

    // 2. Insert new Property 5 (Pending verification in Colombo)
    $stmtProp5 = $pdo->prepare("INSERT INTO properties (landlord_id, city, address, structural_type, latitude, longitude, maps_link, facilities) VALUES (?, 'Colombo', '14/C, Campus Heights, Katubedda, Moratuwa', 'Annex', 6.797284, 79.901768, 'https://maps.google.com/?q=6.797284,79.901768', 'Water, Electricity, Parking, Wi-Fi')");
    $stmtProp5->execute(array($landlord_id));
    $prop5_id = $pdo->lastInsertId();

    // Add rooms for Property 5
    $stmtRoom5 = $pdo->prepare("INSERT INTO rooms (property_id, room_type, slot_capacity, price, security_deposit, bathroom_access, wifi_available) VALUES (?, 'single', 1, 19500.00, 39000.00, 'attached', 1)");
    $stmtRoom5->execute(array($prop5_id));

    // Insert Task for Property 5 (Pending Unclaimed)
    $stmtTask5 = $pdo->prepare("INSERT INTO agent_tasks (agent_id, property_id, task_type, status) VALUES (NULL, ?, 'verification', 'pending')");
    $stmtTask5->execute(array($prop5_id));
    $task5_id = $pdo->lastInsertId();

    // 3. Insert new Property 6 (Pending verification in Colombo)
    $stmtProp6 = $pdo->prepare("INSERT INTO properties (landlord_id, city, address, structural_type, latitude, longitude, maps_link, facilities) VALUES (?, 'Colombo', '99/A, Station Road, Dehiwala, Colombo', 'Apartment', 6.851234, 79.865432, 'https://maps.google.com/?q=6.851234,79.865432', 'Water, AC, Security, Generator')");
    $stmtProp6->execute(array($landlord_id));
    $prop6_id = $pdo->lastInsertId();

    $stmtRoom6 = $pdo->prepare("INSERT INTO rooms (property_id, room_type, slot_capacity, price, security_deposit, bathroom_access, wifi_available) VALUES (?, 'shared', 2, 14000.00, 28000.00, 'shared', 1)");
    $stmtRoom6->execute(array($prop6_id));

    // Insert Task for Property 6 (Pending Unclaimed)
    $stmtTask6 = $pdo->prepare("INSERT INTO agent_tasks (agent_id, property_id, task_type, status) VALUES (NULL, ?, 'verification', 'pending')");
    $stmtTask6->execute(array($prop6_id));

    // 4. Assign Task #1 and Complaint #1 to agent 4 and agent 1 so both Colombo agents see data
    $pdo->exec("UPDATE agent_tasks SET agent_id = 4 WHERE task_id = 1");
    $pdo->exec("UPDATE agent_tasks SET agent_id = 4 WHERE task_id = 3");
    
    // 5. Insert sample Complaint for Colombo agents
    $stmtComp = $pdo->prepare("INSERT INTO complaints (student_id, property_id, description, category, status, assigned_agent_id) VALUES (?, 1, 'Landlord increased monthly utility charges without prior notice after signing lease.', 'fee_discrepancy', 'investigating', 4)");
    $stmtComp->execute(array($student_id));

    // 6. Insert sample Completed Verification Report for Task 3 if missing
    $checkReport = $pdo->prepare("SELECT report_id FROM verification_reports WHERE task_id = 3");
    $checkReport->execute();
    if (!$checkReport->fetch()) {
        $stmtRep = $pdo->prepare("INSERT INTO verification_reports (task_id, structural_safety, electrical_safety, fire_exit, gps_match, neighborhood_safety, furnishing_match, bathroom_match, wifi_match, finance_match, photo_path_1, photo_path_2, agent_comments) VALUES (3, 1, 1, 1, 1, 4, 1, 1, 1, 1, '/boardnest/public/uploads/test_room1.jpg', '/boardnest/public/uploads/test_room2.jpg', 'All structural and safety checks verified on site. Landlord fully cooperative.')");
        $stmtRep->execute();
    }

    echo "✅ Demo tasks and complaints seeded successfully for Colombo Field Agents!\n";
} catch (Exception $e) {
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
}
?>
