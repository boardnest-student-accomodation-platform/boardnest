<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../modules/students/services/portal.php';
requireRole('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !studentPortalValidCsrf((string) ($_POST['csrf_token'] ?? ''))) { header('Location: ../profile.php'); exit(); }
$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');
$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
if (!$profile || !password_verify($current, $profile['password_hash'])) {
    $_SESSION['flash']['error'] = 'Your current password is incorrect.';
} elseif (strlen($new) < 8) {
    $_SESSION['flash']['error'] = 'The new password must contain at least 8 characters.';
} elseif ($new !== $confirm) {
    $_SESSION['flash']['error'] = 'The new passwords do not match.';
} else {
    $statement = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
    $statement->execute([password_hash($new, PASSWORD_DEFAULT), (int) $_SESSION['user_id']]);
    $_SESSION['flash']['success'] = 'Password updated successfully.';
}
header('Location: ../profile.php'); exit();
