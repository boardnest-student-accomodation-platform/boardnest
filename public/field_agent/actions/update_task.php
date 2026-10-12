<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/update_task.php
require_once '../../../includes/session.php';
requireRole('field_agent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

// Parse request parameters
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$action_type = isset($_POST['action_type']) ? $_POST['action_type'] : '';

$allowed_actions = array('claim', 'withdraw', 'suspend', 'verify_geofence');
if (!in_array($action_type, $allowed_actions, true)) {
    $_SESSION['error'] = 'Invalid task action.';
    header('Location: ../dashboard.php');
    exit();
}

$csrf_token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    $_SESSION['error'] = 'The request expired. Please try again.';
    header('Location: ../dashboard.php');
    exit();
}

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/update_task.php';
?>
