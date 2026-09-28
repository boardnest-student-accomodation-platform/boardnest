<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/submit_complaint_report.php
require_once '../../../includes/session.php';
requireRole('field_agent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php?tab=complaints');
    exit();
}

$csrf_token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    $_SESSION['error'] = 'The complaint report form expired. Please try again.';
    header('Location: ../dashboard.php?tab=complaints');
    exit();
}

// Parse request parameters
$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$findings = isset($_POST['findings']) ? trim($_POST['findings']) : '';
$recommendation = isset($_POST['recommendation']) ? $_POST['recommendation'] : '';
$visit_fee = isset($_POST['visit_fee']) ? floatval($_POST['visit_fee']) : 0.00;

$valid_recommendations = array('resolved', 'dismissed', 'upheld', 'escalated');
if ($complaint_id < 1 || $findings === '' || !in_array($recommendation, $valid_recommendations, true) || $visit_fee < 0) {
    $_SESSION['error'] = 'Complete the findings, recommendation, and valid visit fee before submitting.';
    header('Location: ../task_view.php?complaint_id=' . $complaint_id);
    exit();
}

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/submit_complaint_report.php';
?>
