<?php
// Core server-side logic for submit_complaint_report
// Path: src/field_agent/actions/submit_complaint_report.php
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

$complaint_id   = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$findings       = isset($_POST['findings']) ? trim($_POST['findings']) : '';
$recommendation = isset($_POST['recommendation']) ? trim($_POST['recommendation']) : '';
$visit_fee      = isset($_POST['visit_fee']) ? floatval($_POST['visit_fee']) : 0;

// Fetch and verify complaint is assigned to this agent
$stmtComp = $pdo->prepare("
    SELECT c.* FROM complaints c 
    INNER JOIN complaint_investigations ci ON c.complaint_id = ci.complaint_id 
    WHERE c.complaint_id = ? AND ci.field_agent_user_id = ?
");
$stmtComp->execute(array($complaint_id, $_SESSION['user_id']));
$complaint = $stmtComp->fetch();

if (!$complaint) {
    $_SESSION['error'] = 'Complaint not found or not assigned to you.';
    header('Location: ../dashboard.php');
    exit();
}

// Server enforcement of Geofence Authorization
if (!isset($_SESSION['geofence_passed_comp_' . $complaint_id])) {
    $_SESSION['error'] = 'GPS Geofence authorization is missing. Please verify your location first.';
    header('Location: ../task_view.php?complaint_id=' . $complaint_id);
    exit();
}
$geo_data = $_SESSION['geofence_passed_comp_' . $complaint_id];
if (!is_array($geo_data) || !isset($geo_data['expires']) || time() > $geo_data['expires']) {
    unset($_SESSION['geofence_passed_comp_' . $complaint_id]);
    $_SESSION['error'] = 'GPS Geofence authorization has expired (1 hour limit). Please verify your location again.';
    header('Location: ../task_view.php?complaint_id=' . $complaint_id);
    exit();
}

try {
    $pdo->beginTransaction();

    // Update complaint status
    $stmtUpdate = $pdo->prepare("
        UPDATE complaints 
        SET status = ? 
        WHERE complaint_id = ?
    ");
    $stmtUpdate->execute(array($recommendation, $complaint_id));

    // Update investigation details
    $stmtUpdateInv = $pdo->prepare("
        UPDATE complaint_investigations 
        SET findings = ?, visit_fee_charged = ? 
        WHERE complaint_id = ? AND field_agent_user_id = ?
    ");
    $stmtUpdateInv->execute(array($findings, $visit_fee, $complaint_id, $_SESSION['user_id']));

    $pdo->commit();

    $_SESSION['success'] = 'Complaint investigation report submitted successfully!';
    header('Location: ../dashboard.php?tab=complaints');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: ../task_view.php?complaint_id=' . $complaint_id);
    exit();
}
?>
