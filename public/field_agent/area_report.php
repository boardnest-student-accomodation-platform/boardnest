<?php
// ============================================================
// BoardNest — Area Report (Orchestrator)
// public/field_agent/area_report.php
// ============================================================
require_once '../../includes/session.php';
requireRole('field_agent');
require_once '../../config/db.php';

// Fetch agent
$stmt = $pdo->prepare("SELECT agent_id, assigned_city FROM field_agents WHERE user_id = ?");
$stmt->execute(array($_SESSION['user_id']));
$agent = $stmt->fetch();
if (!$agent) die("Field agent account not found.");
$agent_id = $agent['agent_id'];
$city     = $agent['assigned_city'];

$task_id  = isset($_GET['task_id'])  ? intval($_GET['task_id'])  : 0;

// Fetch previous reports
$stmtHistory = $pdo->prepare("SELECT * FROM area_reports WHERE agent_id = ? ORDER BY submitted_at DESC");
$stmtHistory->execute(array($agent_id));
$reports = $stmtHistory->fetchAll();

$success_msg = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_msg   = isset($_SESSION['error'])   ? $_SESSION['error']   : '';
unset($_SESSION['success'], $_SESSION['error']);

define('PARTIALS_AR', __DIR__ . '/partials/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardNest — Area Observations</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/field_agent.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="fa-dashboard-body">

    <!-- Navbar -->
    <header class="navbar-custom">
        <a href="../../index.html" class="fa-report-brand">BoardNest</a>
        <div class="fa-header-actions">
            <span class="fa-report-role">
                📍 <?php echo htmlspecialchars($city); ?> Regional Agent
            </span>
            <?php if ($task_id > 0): ?>
                <a href="task_view.php?task_id=<?php echo $task_id; ?>" class="fa-report-btn-primary">
                    ← Back to Audit Task #VT-<?php echo $task_id; ?>
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="fa-report-btn-secondary">
                ← Back to Dashboard
            </a>
        </div>
    </header>

    <div class="main-container">
        <!-- Centered single-column layout container -->
        <div class="fa-report-wrapper">
            <!-- Hero Banner -->
            <div class="hero-banner-card fa-mb-24">
                <div>
                    <span class="fa-hero-badge">
                        📍 Regional Profile Audit — <?php echo htmlspecialchars($city); ?>
                    </span>
                    <h1 class="fa-hero-title">Area Profile Audit &amp; Observations</h1>
                    <p class="fa-hero-desc">
                        Log transport infrastructure, student amenities, and neighborhood security conditions to help students make safe housing choices.
                    </p>
                </div>
                <div class="fa-hero-stat-card">
                    <div class="fa-hero-stat-value">100%</div>
                    <div class="fa-hero-stat-label">Verified Coverage</div>
                </div>
            </div>

            <!-- Flash Alerts -->
            <?php if ($success_msg): ?>
                <div class="fa-alert fa-alert-success fa-mb-24">
                    ✅ <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="fa-alert fa-alert-danger fa-mb-24">
                    ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <?php require PARTIALS_AR . 'area_report_form.php'; ?>
        </div>
    </div>

    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
