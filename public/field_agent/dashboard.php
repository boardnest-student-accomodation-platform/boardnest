<?php
// ============================================================
// BoardNest — Field Agent Dashboard (Orchestrator)
// public/field_agent/dashboard.php
// ============================================================
require_once '../../includes/session.php';
requireRole('field_agent');
require_once '../../config/db.php';
require_once '../../src/field_agent/db_init.php';

// Fetch agent
$stmt = $pdo->prepare("SELECT agent_id, assigned_city FROM field_agents WHERE user_id = ?");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();
if (!$agent) die("Field agent account not found.");
$agent_id = $agent['agent_id'];
$city     = $agent['assigned_city'];

$active_tab  = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_msg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

// Pending tasks (unclaimed in city)
$stmtPending = $pdo->prepare("
    SELECT t.*, p.address, p.structural_type, p.city
    FROM agent_tasks t
    INNER JOIN properties p ON t.property_id = p.property_id
    WHERE t.agent_id IS NULL AND t.status = 'pending' AND p.city = ? AND t.task_type = 'verification'
");
$stmtPending->execute(array($city));
$pending_tasks = $stmtPending->fetchAll();

// Claimed tasks (in progress)
$stmtClaimed = $pdo->prepare("
    SELECT t.*, p.address, p.structural_type, p.city
    FROM agent_tasks t
    INNER JOIN properties p ON t.property_id = p.property_id
    WHERE t.agent_id = ? AND t.status = 'in_progress' AND t.task_type = 'verification'
");
$stmtClaimed->execute(array($agent_id));
$claimed_tasks = $stmtClaimed->fetchAll();

// Completed tasks
$stmtCompleted = $pdo->prepare("
    SELECT t.*, p.address, p.structural_type, p.city, r.submitted_at
    FROM agent_tasks t
    INNER JOIN properties p ON t.property_id = p.property_id
    LEFT JOIN  verification_reports r ON t.task_id = r.task_id
    WHERE t.agent_id = ? AND t.status = 'completed' AND t.task_type = 'verification'
");
$stmtCompleted->execute(array($agent_id));
$completed_tasks = $stmtCompleted->fetchAll();

// Assigned complaints
$stmtComplaints = $pdo->prepare("
    SELECT c.*, p.address, p.structural_type, u.full_name AS student_name
    FROM complaints c
    INNER JOIN properties p ON c.property_id = p.property_id
    INNER JOIN students   s ON c.student_id   = s.student_id
    INNER JOIN users      u ON s.user_id       = u.user_id
    WHERE c.assigned_agent_id = ? AND c.status IN ('assigned','investigating')
");
$stmtComplaints->execute(array($agent_id));
$complaints_tasks = $stmtComplaints->fetchAll();

$count_pending   = count($pending_tasks);
$count_claimed   = count($claimed_tasks);
$count_completed = count($completed_tasks);
$count_complaints = count($complaints_tasks);

define('PARTIALS', __DIR__ . '/partials/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Field Agent Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
</head>
<body style="font-family:'Plus Jakarta Sans',sans-serif;background-color:#FAF7F2;color:#3B3330;margin:0;">

    <!-- Navbar -->
    <header class="navbar-custom">
        <a href="../../index.html" class="navbar-brand-custom">BoardNest</a>
        <div class="navbar-user-pill">
            <button type="button" onclick="openAgentGuide()" style="background:#FFF8F5;border:1.5px solid #E8DDD4;color:#6F4E37;padding:4px 12px;border-radius:50px;font-size:11px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:4px;margin-right:6px;">
                📖 Inspection Guide
            </button>
            <div class="user-avatar-circle"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
            <div style="font-size:13px;font-weight:700;color:#3B3330;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
            <span style="display:inline-block;background:#3B3330;color:#FFFFFF;font-size:11px;font-weight:700;padding:3px 10px;border-radius:50px;"><?php echo htmlspecialchars($city); ?> Agent</span>
            <a href="logout.php" style="color:#C0392B;text-decoration:none;font-size:12px;font-weight:700;padding-left:8px;border-left:1px solid #E8DDD4;">Logout</a>
        </div>
    </header>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid-layout">
        <?php require PARTIALS . '_dashboard_sidebar.php'; ?>
        <?php require PARTIALS . '_dashboard_main.php'; ?>
    </div>

    <?php require '../../includes/agent_guide_modal.php'; ?>

    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
