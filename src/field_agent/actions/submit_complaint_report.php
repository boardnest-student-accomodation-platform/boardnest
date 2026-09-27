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

// Fetch and verify complaint is assigned to this agent
$stmtComp = $pdo->prepare("SELECT * FROM complaints WHERE complaint_id = ? AND assigned_agent_id = ?");
$stmtComp->execute(array($complaint_id, $agent_id));
$complaint = $stmtComp->fetch();

if (!$complaint) {
    $_SESSION['error'] = 'Complaint not found or not assigned to you.';
    header('Location: ../dashboard.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Update complaint with findings, recommendation, visit fee, and mark status as resolved
    $stmtUpdate = $pdo->prepare("
        UPDATE complaints 
        SET findings = ?, recommendation = ?, visit_fee_charged = ?, status = 'resolved' 
        WHERE complaint_id = ?
    ");
    $stmtUpdate->execute(array($findings, $recommendation, $visit_fee, $complaint_id));

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
