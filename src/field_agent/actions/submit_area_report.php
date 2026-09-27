<?php
// Core server-side logic for submit_area_report
// Path: src/field_agent/actions/submit_area_report.php
require_once __DIR__ . '/../../../config/db.php';

// Fetch agent details
$stmt = $pdo->prepare("SELECT agent_id, assigned_city FROM field_agents WHERE user_id = ?");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();

if (!$agent) {
    $_SESSION['error'] = 'Field agent account not found.';
    header('Location: ../dashboard.php');
    exit();
}
$agent_id = $agent['agent_id'];
$city = $agent['assigned_city'];

try {
    // Insert Area Report (Create operation)
    $stmtInsert = $pdo->prepare("
        INSERT INTO area_reports (agent_id, city, transport_details, amenities_details, safety_details, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmtInsert->execute(array($agent_id, $city, $transport, $amenities, $safety));

    $_SESSION['success'] = 'Area report submitted successfully! It is now awaiting Admin review.';
    header('Location: ../area_report.php');
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: ../area_report.php');
    exit();
}
?>
