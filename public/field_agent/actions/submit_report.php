<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/submit_report.php
require_once '../../../includes/session.php';
requireRole('field_agent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

$csrf_token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    $_SESSION['error'] = 'The report form expired. Please try again.';
    header('Location: ../dashboard.php');
    exit();
}

// Parse request parameters
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/submit_report.php';
?>
