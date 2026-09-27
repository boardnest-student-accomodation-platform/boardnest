<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/update_task.php
require_once '../../../includes/session.php';
requireRole('field_agent');

// Parse request parameters
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$action_type = isset($_POST['action_type']) ? $_POST['action_type'] : '';
$geofence_override = isset($_POST['geofence_override']) ? intval($_POST['geofence_override']) : 0;

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/update_task.php';
?>
