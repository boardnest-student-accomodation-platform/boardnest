<?php
// ============================================================
// BoardNest — Field Agent Task View (Orchestrator)
// public/field_agent/task_view.php
// All logic in src/field_agent/ | Partials in partials/
// ============================================================
require_once '../../includes/session.php';
requireRole('field_agent');
require_once '../../config/db.php';

$task_id      = isset($_GET['task_id'])      ? intval($_GET['task_id'])      : 0;
$complaint_id = isset($_GET['complaint_id']) ? intval($_GET['complaint_id']) : 0;

// Fetch agent
$stmt = $pdo->prepare("SELECT agent_id, assigned_city FROM field_agents WHERE user_id = ?");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();
if (!$agent) die("Field agent account not found.");
$agent_id = $agent['agent_id'];
$city     = $agent['assigned_city'];

// Flash messages
$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_msg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

// Load data based on mode
if ($complaint_id > 0) {
    $stmtComp = $pdo->prepare("
        SELECT c.*, p.address, p.structural_type, p.latitude, p.longitude,
               u.full_name AS student_name, s.mobile AS student_mobile
        FROM complaints c
        INNER JOIN properties p ON c.property_id = p.property_id
        INNER JOIN students   s ON c.student_id   = s.student_id
        INNER JOIN users      u ON s.user_id       = u.user_id
        WHERE c.complaint_id = ? AND c.assigned_agent_id = ?
    ");
    $stmtComp->execute(array($complaint_id, $agent_id));
    $complaint = $stmtComp->fetch();
    if (!$complaint) die("Complaint not found or not assigned to you.");

} elseif ($task_id > 0) {
    $stmtTask = $pdo->prepare("
        SELECT t.*, p.address, p.structural_type, p.city, p.latitude, p.longitude,
               p.maps_link, p.facilities, p.property_id
        FROM agent_tasks t
        INNER JOIN properties p ON t.property_id = p.property_id
        WHERE t.task_id = ? AND (t.agent_id = ? OR t.agent_id IS NULL)
    ");
    $stmtTask->execute(array($task_id, $agent_id));
    $task = $stmtTask->fetch();
    if (!$task) die("Verification task not found or not assigned to you.");

    $stmtRooms = $pdo->prepare("SELECT * FROM rooms WHERE property_id = ?");
    $stmtRooms->execute(array($task['property_id']));
    $rooms = $stmtRooms->fetchAll();

    $report = null;
    if ($task['status'] === 'completed') {
        $stmtRep = $pdo->prepare("SELECT * FROM verification_reports WHERE task_id = ?");
        $stmtRep->execute(array($task_id));
        $report = $stmtRep->fetch();
    }
} else {
    header('Location: dashboard.php');
    exit();
}

// Shorthand for component paths
define('PARTIALS', __DIR__ . '/partials/');
define('MODALS',   __DIR__ . '/../../src/field_agent/components/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Task Audit Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
</head>
<body>
    <?php require PARTIALS . '_header.php'; ?>

    <div class="main-container">

        <?php if ($success_msg): ?>
            <div style="background:#D1E7DD;color:#0F5132;border:1px solid #BADBCE;padding:14px 20px;border-radius:12px;font-weight:600;font-size:13px;margin-bottom:16px;">
                ✅ <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div style="background:#F8D7DA;color:#842029;border:1px solid #F5C2C7;padding:14px 20px;border-radius:12px;font-weight:600;font-size:13px;margin-bottom:16px;">
                ⚠️ <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($complaint_id > 0): ?>
            <?php 
            $show_complaint_part = 'header';
            require PARTIALS . '_complaint_view.php'; 
            ?>

            <?php if (isset($complaint['status']) && $complaint['status'] === 'resolved'): ?>
                <?php 
                $show_complaint_part = 'form';
                require PARTIALS . '_complaint_view.php'; 
                ?>
            <?php else: ?>
                <?php require PARTIALS . '_gps_geofence.php'; ?>

                <?php if (isset($_SESSION['geofence_passed_comp_' . $complaint_id])): ?>
                    <?php 
                    $show_complaint_part = 'form';
                    require PARTIALS . '_complaint_view.php'; 
                    ?>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <?php require PARTIALS . '_checklist_item.php'; // loads renderChecklistItem() ?>

            <div class="audit-two-column-layout">
                <!-- LEFT: Sticky property context -->
                <div class="left-property-sidebar">
                    <?php require PARTIALS . '_property_sidebar.php'; ?>
                </div>

                <!-- RIGHT: GPS gate + Audit form -->
                <div>
                    <?php if (isset($task['status']) && $task['status'] === 'completed'): ?>
                        <?php require PARTIALS . '_audit_form.php'; ?>
                    <?php else: ?>
                        <?php require PARTIALS . '_gps_geofence.php'; ?>

                        <?php if (isset($_SESSION['geofence_passed_' . $task_id])): ?>
                            <?php require PARTIALS . '_audit_form.php'; ?>
                        <?php endif; ?>

                        <?php require PARTIALS . '_emergency_suspension.php'; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Modals -->
    <?php require MODALS . 'live_camera_modal.php'; ?>
    <?php require MODALS . 'agent_guide_modal.php'; ?>

    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
