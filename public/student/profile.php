<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/portal.php';
requireRole('student');
$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
if (!$profile) { header('Location: ../../login.php?account_status=profile_missing'); exit(); }
$dashboardNotificationCount = studentPortalUnreadCount($pdo, (int) $_SESSION['user_id']);
$studentActivePage = 'profile';
$csrfToken = studentPortalEnsureCsrf();
$success = $_SESSION['flash']['success'] ?? '';
$error = $_SESSION['flash']['error'] ?? '';
unset($_SESSION['flash']['success'], $_SESSION['flash']['error']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Profile | BoardNest</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/student-dashboard.css"><link rel="stylesheet" href="../assets/css/student-portal.css">
</head><body class="student-dashboard-page student-portal-page">
<?php require __DIR__ . '/partials/dashboard_header.php'; ?>
<main class="student-portal-shell">
    <header class="student-portal-title"><div><span>Student Account</span><h1>My Profile</h1><p>Review your verified identity and academic information.</p></div></header>
    <?php if ($success): ?><div class="student-portal-alert is-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="student-portal-alert is-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="student-profile-layout">
        <section class="student-portal-panel">
            <div class="student-profile-summary"><span><?= htmlspecialchars(studentPortalInitials($profile['full_name'])) ?></span><div><h2><?= htmlspecialchars($profile['full_name']) ?></h2><p><?= $profile['verf_tier'] === 'tier2' ? 'Verified Student (Tier 2)' : 'Pending Verification (Tier 1)' ?></p></div></div>
            <dl class="student-profile-details"><div><dt>Full Name</dt><dd><?= htmlspecialchars($profile['full_name']) ?></dd></div><div><dt>Email</dt><dd><?= htmlspecialchars($profile['email']) ?><small>Login identifier cannot be changed</small></dd></div><div><dt>Mobile</dt><dd><?= htmlspecialchars($profile['mobile'] ?: 'Not provided') ?></dd></div><div><dt>University</dt><dd><?= htmlspecialchars($profile['university'] ?: 'Not provided') ?></dd></div><div><dt>Academic Year</dt><dd><?= htmlspecialchars($profile['academic_year'] ?: 'Not provided') ?></dd></div><div><dt>NIC Number</dt><dd><?= htmlspecialchars($profile['nic_number'] ?: 'Not provided') ?><small>Only Admin can correct identity details</small></dd></div><div><dt>Verification Tier</dt><dd><?= $profile['verf_tier'] === 'tier2' ? 'Tier 2 - Verified' : 'Tier 1 - Pending' ?></dd></div></dl>
        </section>
        <section class="student-portal-panel" id="change-password"><div class="student-portal-section-heading"><div><h2>Change Password</h2><p>Confirm your current password before setting a new one.</p></div></div>
            <form class="student-portal-form is-single" action="actions/update_password.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><label><span>Current Password <b>*</b></span><input type="password" name="current_password" autocomplete="current-password" required></label><label><span>New Password <b>*</b></span><input type="password" name="new_password" minlength="8" autocomplete="new-password" required></label><label><span>Confirm Password <b>*</b></span><input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label><button class="student-portal-primary" type="submit">Update Password</button></form>
        </section>
    </div>
</main>
<footer class="student-dashboard-footer"><div><strong>BoardNest</strong><p>&copy; <?= date('Y') ?> BoardNest. Student support and safer accommodation.</p></div><nav><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Support</a></nav></footer><script src="../assets/js/student-dashboard.js"></script></body></html>
