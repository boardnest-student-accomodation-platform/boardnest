<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../modules/students/services/portal.php';
requireRole('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !studentPortalValidCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    header('Location: ../notifications.php'); exit();
}
$notificationId = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
$statement = $pdo->prepare('SELECT link_url FROM notifications WHERE notification_id = ? AND user_id = ?');
$statement->execute([(int) $notificationId, (int) $_SESSION['user_id']]);
$link = $statement->fetchColumn();
if ($link !== false) {
    $update = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?');
    $update->execute([(int) $notificationId, (int) $_SESSION['user_id']]);
}
$allowedLinks = ['dashboard.php', 'complaints.php', 'notifications.php', 'profile.php'];
$destination = in_array((string) $link, $allowedLinks, true) ? (string) $link : 'notifications.php';
header('Location: ../' . $destination); exit();
