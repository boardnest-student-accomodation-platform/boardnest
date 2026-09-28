<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/students/services/portal.php';
requireRole('student');
$notifications = studentPortalNotifications($pdo, (int) $_SESSION['user_id']);
$dashboardNotificationCount = studentPortalUnreadCount($pdo, (int) $_SESSION['user_id']);
$studentActivePage = 'notifications';
$csrfToken = studentPortalEnsureCsrf();
$success = $_SESSION['flash']['success'] ?? '';
unset($_SESSION['flash']['success']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications | BoardNest</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/student-dashboard.css"><link rel="stylesheet" href="../assets/css/student-portal.css">
</head><body class="student-dashboard-page student-portal-page">
<?php require __DIR__ . '/partials/dashboard_header.php'; ?>
<main class="student-portal-shell">
    <header class="student-portal-title student-portal-title--actions"><div><span>Account Updates</span><h1>Notifications</h1><p>Booking decisions, complaint updates, announcements, and account changes.</p></div><?php if ($dashboardNotificationCount > 0): ?><form action="actions/mark_notifications_read.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><button class="student-portal-secondary" type="submit">Mark All as Read</button></form><?php endif; ?></header>
    <?php if ($success): ?><div class="student-portal-alert is-success" role="status"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <section class="student-notification-list" aria-label="Notifications">
        <?php if ($notifications): ?>
            <?php foreach ($notifications as $notification): ?><?php [$typeLabel, $icon] = studentPortalNotificationMeta($notification['type']); ?>
                <form class="student-notification-card <?= (int) $notification['is_read'] === 0 ? 'is-unread' : '' ?>" action="actions/read_notification.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="notification_id" value="<?= (int) $notification['notification_id'] ?>">
                    <button type="submit"><span class="student-notification-icon" aria-hidden="true"><?= $icon ?></span><span class="student-notification-copy"><strong><?= htmlspecialchars($typeLabel) ?></strong><span><?= htmlspecialchars($notification['message']) ?></span><small><?= htmlspecialchars(date('j M Y, g:i a', strtotime($notification['created_at']))) ?></small></span><span class="student-read-state"><?= (int) $notification['is_read'] === 0 ? 'Unread' : 'Read' ?></span></button>
                </form>
            <?php endforeach; ?>
        <?php else: ?><div class="student-portal-panel student-portal-empty"><strong>You have no notifications yet.</strong><p>Updates about bookings, complaints, and your account will appear here.</p></div><?php endif; ?>
    </section>
</main>
<footer class="student-dashboard-footer"><div><strong>BoardNest</strong><p>&copy; <?= date('Y') ?> BoardNest. Student support and safer accommodation.</p></div><nav><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Support</a></nav></footer>
<script src="../assets/js/student-dashboard.js"></script></body></html>
