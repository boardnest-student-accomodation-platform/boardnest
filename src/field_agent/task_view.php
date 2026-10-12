<?php
// ============================================================
// BoardNest — Field Agent Task View Logic
// src/field_agent/task_view.php
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
requireRole('field_agent');
require_once __DIR__ . '/../../config/db.php';

$task_id      = isset($_GET['task_id'])      ? intval($_GET['task_id'])      : 0;
$complaint_id = isset($_GET['complaint_id']) ? intval($_GET['complaint_id']) : 0;

// Fetch agent
$stmt = $pdo->prepare("
    SELECT f.agent_id, f.assigned_city 
    FROM field_agents f
    INNER JOIN users u ON f.user_id = u.user_id 
    WHERE f.user_id = ? AND f.is_active = 1 AND u.status = 'active'
");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();
if (!$agent) die("Field agent account not found.");
$agent_id = $agent['agent_id'];
$city     = $agent['assigned_city'];

// Flash messages
$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_msg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

// Load data based on mode
if ($complaint_id > 0) {
    $stmtComp = $pdo->prepare("
        SELECT c.*, ci.findings, ci.visit_fee_charged,
               p.address, p.structural_type, p.latitude, p.longitude,
               u.full_name AS student_name, s.mobile AS student_mobile
        FROM complaints c
        INNER JOIN listings l ON c.listing_id = l.listing_id
        INNER JOIN properties p ON l.property_id = p.property_id
        INNER JOIN complaint_investigations ci ON c.complaint_id = ci.complaint_id
        INNER JOIN users      u ON c.complainant_user_id = u.user_id
        INNER JOIN students   s ON u.user_id = s.user_id
        WHERE c.complaint_id = ? AND ci.field_agent_user_id = ?
    ");
    $stmtComp->execute(array($complaint_id, $_SESSION['user_id']));
    $complaint = $stmtComp->fetch();
    if (!$complaint) die("Complaint not found or not assigned to you.");

} elseif ($task_id > 0) {
    $stmtTask = $pdo->prepare("
        SELECT t.*, p.address, p.structural_type, p.city, p.latitude, p.longitude,
               p.maps_url AS maps_link, p.facilities, p.property_id
        FROM agent_tasks t
        INNER JOIN properties p ON t.property_id = p.property_id
        WHERE t.task_id = ? AND t.agent_id = ?
    ");
    $stmtTask->execute(array($task_id, $agent_id));
    $task = $stmtTask->fetch();
    if (!$task) die("Verification task not found or not assigned to you.");

    $stmtRooms = $pdo->prepare("SELECT * FROM rooms WHERE property_id = ?");
    $stmtRooms->execute(array($task['property_id']));
    $rooms = $stmtRooms->fetchAll();

    $report = null;
    if ($task['status'] === 'completed') {
        $stmtRep = $pdo->prepare("SELECT * FROM verification_reports WHERE task_id = ?");
        $stmtRep->execute(array($task_id));
        $report = $stmtRep->fetch();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
