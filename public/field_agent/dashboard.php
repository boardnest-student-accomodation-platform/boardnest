<?php
// ============================================================
// BoardNest — Field Agent Dashboard (Orchestrator)
// public/field_agent/dashboard.php
// ============================================================
require_once '../../includes/session.php';
requireRole('field_agent');
require_once '../../config/db.php';

// Fetch agent
$stmt = $pdo->prepare("
    SELECT f.agent_id, f.assigned_city, f.is_active, u.status 
    FROM field_agents f
    INNER JOIN users u ON f.user_id = u.user_id 
    WHERE f.user_id = ?
      AND f.is_active = 1
      AND u.status = 'active'
");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();
if (!$agent) {
    die("Field agent account not found.");
}

$agent_id = $agent['agent_id'];
$city     = $agent['assigned_city'];
$agent_status = 'active';

$allowed_tabs = array('pending', 'claimed', 'complaints', 'history');
$active_tab   = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
if (!in_array($active_tab, $allowed_tabs, true)) {
    $active_tab = 'pending';
}
$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_msg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
    INNER JOIN listings l ON c.listing_id = l.listing_id
    INNER JOIN properties p ON l.property_id = p.property_id
    INNER JOIN complaint_investigations ci ON c.complaint_id = ci.complaint_id
    INNER JOIN users      u ON c.complainant_user_id = u.user_id
    WHERE ci.field_agent_user_id = ? AND c.status IN ('assigned','under_investigation')
");
$stmtComplaints->execute(array($_SESSION['user_id']));
$complaints_tasks = $stmtComplaints->fetchAll();

// Completed complaints (History)
$stmtCompletedComplaints = $pdo->prepare("
    SELECT c.*, p.address, p.structural_type, u.full_name AS student_name, ci.findings
    FROM complaints c
    INNER JOIN listings l ON c.listing_id = l.listing_id
    INNER JOIN properties p ON l.property_id = p.property_id
    INNER JOIN complaint_investigations ci ON c.complaint_id = ci.complaint_id
    INNER JOIN users      u ON c.complainant_user_id = u.user_id
    WHERE ci.field_agent_user_id = ? AND c.status NOT IN ('assigned','under_investigation', 'pending')
    ORDER BY c.complaint_id DESC
");
$stmtCompletedComplaints->execute(array($_SESSION['user_id']));
$completed_complaints = $stmtCompletedComplaints->fetchAll();

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
<body class="fa-dashboard-body">

    <!-- Navbar -->
    <header class="navbar-custom">
        <a href="../../index.html" class="navbar-brand-custom">BoardNest</a>
        <div class="navbar-user-pill">
            <button type="button" onclick="openAgentGuide()" class="fa-nav-btn">
                📖 Inspection Guide
            </button>
            <div class="user-avatar-circle"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
            <div class="fa-nav-username"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
            <span class="fa-nav-role"><?php echo htmlspecialchars($city); ?> Agent</span>
            <a href="../../logout.php" class="fa-nav-logout">Logout</a>
        </div>
    </header>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid-layout">
        <?php require PARTIALS . '_dashboard_sidebar.php'; ?>
        <?php require PARTIALS . '_dashboard_main.php'; ?>
    </div>

    <?php require __DIR__ . '/../../src/field_agent/components/agent_guide_modal.php'; ?>

    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
