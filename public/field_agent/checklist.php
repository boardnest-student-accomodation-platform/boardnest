<?php
require_once '../../includes/session.php';
requireRole('field_agent');

// The checklist is integrated directly into task_view.php inline to allow seamless single-page geofence verification and submission.
// If accessed directly, redirect back to dashboard.
$_SESSION['error'] = 'Please select a task from the dashboard to perform checklist verification.';
header('Location: dashboard.php');
exit();
?>
