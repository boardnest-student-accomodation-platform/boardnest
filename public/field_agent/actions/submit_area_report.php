<?php
// Public Web-Accessible Entry Point / Routing
// Path: public/field_agent/actions/submit_area_report.php
require_once '../../../includes/session.php';
requireRole('field_agent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../area_report.php');
    exit();
}

$csrf_token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    $_SESSION['error'] = 'The area report form expired. Please try again.';
    header('Location: ../area_report.php');
    exit();
}

// Parse request parameters
$transport = isset($_POST['transport_details']) ? trim($_POST['transport_details']) : '';
$amenities = isset($_POST['amenities_details']) ? trim($_POST['amenities_details']) : '';
$safety = isset($_POST['safety_details']) ? trim($_POST['safety_details']) : '';

if (empty($transport) || empty($amenities) || empty($safety)) {
    $_SESSION['error'] = 'All details (transport, amenities, safety) are required.';
    header('Location: ../area_report.php');
    exit();
}

// Load backend logic core
require_once __DIR__ . '/../../../src/field_agent/actions/submit_area_report.php';
?>
