<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/submit_complaint_report.php
require_once '../../../includes/session.php';
requireRole('field_agent');

// Parse request parameters
$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$findings = isset($_POST['findings']) ? trim($_POST['findings']) : '';
$recommendation = isset($_POST['recommendation']) ? $_POST['recommendation'] : '';
$visit_fee = isset($_POST['visit_fee']) ? floatval($_POST['visit_fee']) : 0.00;

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/submit_complaint_report.php';
?>
