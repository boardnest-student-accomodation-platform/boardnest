<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/portal.php';
requireRole('student');

$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
if (!$profile) { header('Location: ../../login.php?account_status=profile_missing'); exit(); }
$eligibleListings = studentPortalEligibleComplaintListings($pdo, (int) $profile['student_id']);
$complaints = studentPortalComplaints($pdo, (int) $_SESSION['user_id']);
$categories = studentPortalComplaintCategories();
$dashboardNotificationCount = studentPortalUnreadCount($pdo, (int) $_SESSION['user_id']);
$studentActivePage = 'complaints';
$csrfToken = studentPortalEnsureCsrf();
$success = $_SESSION['flash']['success'] ?? '';
$error = $_SESSION['flash']['error'] ?? '';
unset($_SESSION['flash']['success'], $_SESSION['flash']['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints | BoardNest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css"><link rel="stylesheet" href="../assets/css/student-portal.css">
</head>
<body class="student-dashboard-page student-portal-page">
<?php require __DIR__ . '/partials/dashboard_header.php'; ?>
<main class="student-portal-shell">
    <header class="student-portal-title"><div><span>Support &amp; Safety</span><h1>Complaints</h1><p>Report issues connected to accommodation you have stayed at or currently occupy.</p></div></header>
    <?php if ($success): ?><div class="student-portal-alert is-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="student-portal-alert is-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="student-portal-panel">
        <div class="student-portal-section-heading"><div><h2>Submit a Complaint</h2><p>Provide enough detail for the moderation team to understand and investigate the issue.</p></div></div>
        <?php if ($eligibleListings): ?>
            <form class="student-portal-form" action="actions/submit_complaint.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <label><span>Select Listing <b>*</b></span><select name="listing_id" required><option value="">Choose a confirmed stay</option><?php foreach ($eligibleListings as $listing): ?><option value="<?= (int) $listing['listing_id'] ?>"><?= htmlspecialchars($listing['title'] . ' - ' . $listing['city']) ?></option><?php endforeach; ?></select></label>
                <label><span>Category <b>*</b></span><select name="category" required><option value="">Select a category</option><?php foreach ($categories as $value => $label): ?><option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option><?php endforeach; ?></select></label>
                <label class="is-wide"><span>Description <b>*</b></span><textarea name="description" rows="6" minlength="20" maxlength="3000" placeholder="Describe what happened, when it occurred, and any relevant details..." required></textarea><small>Minimum 20 characters</small></label>
                <button class="student-portal-primary" type="submit">Submit Complaint</button>
            </form>
        <?php else: ?>
            <div class="student-portal-empty"><strong>No eligible stays</strong><p>You can file a complaint after a booking is confirmed and linked to your student account.</p><a href="search.php">Browse Listings</a></div>
        <?php endif; ?>
    </section>

    <section class="student-portal-panel">
        <div class="student-portal-section-heading"><div><h2>My Complaints</h2><p><?= count($complaints) ?> complaint<?= count($complaints) === 1 ? '' : 's' ?> filed</p></div></div>
        <?php if ($complaints): ?><div class="student-complaint-list">
            <?php foreach ($complaints as $complaint): ?><?php [$statusLabel, $statusClass] = studentPortalComplaintStatus($complaint['status']); ?>
                <article class="student-complaint-card">
                    <div class="student-complaint-header"><div><span><?= htmlspecialchars($categories[$complaint['category']] ?? ucwords(str_replace('_', ' ', $complaint['category']))) ?></span><h3><?= htmlspecialchars($complaint['listing_title']) ?></h3><small>Filed <?= htmlspecialchars(date('j M Y, g:i a', strtotime($complaint['submitted_at']))) ?></small></div><span class="student-status-badge is-<?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></span></div>
                    <details><summary>View description</summary><p><?= nl2br(htmlspecialchars($complaint['description'])) ?></p></details>
                    <?php if ($complaint['status'] === 'dismissed'): ?><p class="student-dismissed-note">This complaint was reviewed and determined to be outside platform scope. Please resolve this matter directly with your landlord.</p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div><?php else: ?><div class="student-portal-empty"><strong>You have not filed any complaints.</strong><p>If you are experiencing issues with your accommodation, use the form above.</p></div><?php endif; ?>
    </section>
</main>
<footer class="student-dashboard-footer"><div><strong>BoardNest</strong><p>&copy; <?= date('Y') ?> BoardNest. Student support and safer accommodation.</p></div><nav><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Support</a></nav></footer>
<script src="../assets/js/student-dashboard.js"></script>
</body></html>
