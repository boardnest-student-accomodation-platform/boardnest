<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/submit_report.php
require_once '../../../includes/session.php';
requireRole('field_agent');

// Parse request parameters
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/submit_report.php';
?>
