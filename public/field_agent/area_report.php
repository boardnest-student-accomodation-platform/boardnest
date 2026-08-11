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
</head>
<body style="font-family:'Plus Jakarta Sans',sans-serif;background-color:#FAF7F2;color:#3B3330;margin:0;">

    <!-- Navbar -->
    <header class="navbar-custom">
        <a href="../../index.html" style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:900;color:#6F4E37;letter-spacing:-0.8px;text-decoration:none;">BoardNest</a>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="display:inline-block;background:#3B3330;color:#FFFFFF;font-size:11px;font-weight:700;padding:4px 12px;border-radius:50px;">
                📍 <?php echo htmlspecialchars($city); ?> Regional Agent
            </span>
            <?php if ($task_id > 0): ?>
                <a href="task_view.php?task_id=<?php echo $task_id; ?>" style="background:#A4856D;color:#FFFFFF;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:800;text-decoration:none;box-shadow:0 2px 6px rgba(164,133,109,0.25);">
                    ← Back to Audit Task #VT-<?php echo $task_id; ?>
                </a>
            <?php endif; ?>
            <a href="dashboard.php" style="background:#FFF8F5;border:1.5px solid #E8DDD4;color:#3B3330;padding:6px 16px;border-radius:50px;font-size:12px;font-weight:700;text-decoration:none;">
                ← Back to Dashboard
            </a>
        </div>
    </header>

    <div class="main-container">
        <!-- Centered single-column layout container -->
        <div style="max-width:860px;margin:0 auto;">
            <!-- Hero Banner -->
            <div class="hero-banner-card" style="margin-bottom:24px;">
                <div>
                    <span style="display:inline-block;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);color:#FFFFFF;font-size:11px;font-weight:800;padding:4px 14px;border-radius:50px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">
                        📍 Regional Profile Audit — <?php echo htmlspecialchars($city); ?>
                    </span>
                    <h1 style="font-size:28px;font-weight:900;color:#FFFFFF;margin:0 0 8px 0;letter-spacing:-0.5px;">Area Profile Audit &amp; Observations</h1>
                    <p style="font-size:14px;opacity:0.85;margin:0;max-width:600px;line-height:1.5;">
                        Log transport infrastructure, student amenities, and neighborhood security conditions to help students make safe housing choices.
                    </p>
                </div>
                <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.2);padding:16px 24px;border-radius:18px;text-align:center;">
                    <div style="font-size:24px;font-weight:800;color:#2ECC71;">100%</div>
                    <div style="font-size:11px;font-weight:700;opacity:0.8;text-transform:uppercase;">Verified Coverage</div>
                </div>
            </div>

            <!-- Flash Alerts -->
            <?php if ($success_msg): ?>
                <div style="background:rgba(39,174,96,0.08);border:1px solid rgba(39,174,96,0.25);color:#27AE60;padding:14px 20px;border-radius:14px;font-size:14px;font-weight:600;margin-bottom:24px;">
                    ✅ <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div style="background:rgba(192,57,43,0.08);border:1px solid rgba(192,57,43,0.25);color:#C0392B;padding:14px 20px;border-radius:14px;font-size:14px;font-weight:600;margin-bottom:24px;">
                    ⚠️ <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <?php require PARTIALS_AR . '_area_report_form.php'; ?>
        </div>
    </div>

    <script src="../assets/js/field_agent.js"></script>
</body>
</html>
