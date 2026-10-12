<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../modules/students/services/portal.php';
requireRole('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !studentPortalValidCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    $_SESSION['flash']['error'] = 'The remove request expired. Please try again.';
    header('Location: ../saved.php'); exit();
}
$listingId = filter_var($_POST['listing_id'] ?? null, FILTER_VALIDATE_INT);
$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
if (!$listingId || !$profile) {
    $_SESSION['flash']['error'] = 'Invalid saved listing request.';
    header('Location: ../saved.php'); exit();
}
$statement = $pdo->prepare('DELETE FROM saved_listings WHERE student_id = ? AND listing_id = ?');
$statement->execute([(int) $profile['student_id'], (int) $listingId]);
$_SESSION['flash']['success'] = $statement->rowCount() === 1 ? 'Listing removed from your saved list.' : 'The listing was already removed.';
header('Location: ../saved.php'); exit();
