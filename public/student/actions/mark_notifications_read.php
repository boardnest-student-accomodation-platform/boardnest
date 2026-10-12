<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../modules/students/services/portal.php';
requireRole('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !studentPortalValidCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    header('Location: ../notifications.php'); exit();
}
$statement = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
$statement->execute([(int) $_SESSION['user_id']]);
$_SESSION['flash']['success'] = 'All notifications marked as read.';
header('Location: ../notifications.php'); exit();
