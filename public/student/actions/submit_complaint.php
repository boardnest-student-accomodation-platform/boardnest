<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../modules/students/services/portal.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !studentPortalValidCsrf((string) ($_POST['csrf_token'] ?? ''))) {
    $_SESSION['flash']['error'] = 'Your complaint request expired. Please try again.';
    header('Location: ../complaints.php'); exit();
}

$listingId = filter_var($_POST['listing_id'] ?? null, FILTER_VALIDATE_INT);
$category = (string) ($_POST['category'] ?? '');
$description = trim((string) ($_POST['description'] ?? ''));
$categories = studentPortalComplaintCategories();

if (!$listingId || !isset($categories[$category]) || mb_strlen($description) < 20 || mb_strlen($description) > 3000) {
    $_SESSION['flash']['error'] = 'Choose a valid stay, category, and enter a description of at least 20 characters.';
    header('Location: ../complaints.php'); exit();
}

$profile = studentPortalProfile($pdo, (int) $_SESSION['user_id']);
$eligible = $profile ? studentPortalEligibleComplaintListings($pdo, (int) $profile['student_id']) : [];
$eligibleIds = array_map('intval', array_column($eligible, 'listing_id'));
if (!in_array((int) $listingId, $eligibleIds, true)) {
    $_SESSION['flash']['error'] = 'Complaints can only be filed for confirmed stays.';
    header('Location: ../complaints.php'); exit();
}

try {
    $landlordStatement = $pdo->prepare('SELECT ld.user_id FROM listings l INNER JOIN landlords ld ON ld.landlord_id = l.landlord_id WHERE l.listing_id = ?');
    $landlordStatement->execute([(int) $listingId]);
    $landlordUserId = (int) $landlordStatement->fetchColumn();
    if (!$landlordUserId) { throw new RuntimeException('The listing landlord could not be found.'); }
    $statement = $pdo->prepare("INSERT INTO complaints (listing_id, complainant_user_id, landlord_user_id, category, description, status, unverified_stay) VALUES (?, ?, ?, ?, ?, 'new', 0)");
    $statement->execute([(int) $listingId, (int) $_SESSION['user_id'], $landlordUserId, $category, $description]);
    $_SESSION['flash']['success'] = 'Your complaint has been submitted for moderation.';
} catch (Throwable $exception) {
    $_SESSION['flash']['error'] = 'The complaint could not be submitted right now.';
}
header('Location: ../complaints.php'); exit();
