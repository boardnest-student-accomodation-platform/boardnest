<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../config/db.php';

startSession();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: /boardnest/login.php');
    exit();
}

requireRole('student');

$listingId = (int) ($_POST['listing_id'] ?? 0);
$csrfToken = (string) ($_POST['csrf_token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $listingId <= 0) {
    $_SESSION['flash']['error'] = 'Invalid listing request.';
    header('Location: /boardnest/public/student/search.php');
    exit();
}

if (
    empty($_SESSION['csrf_token'])
    || $csrfToken === ''
    || !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    $_SESSION['flash']['error'] = 'The request expired. Please try again.';
    header('Location: /boardnest/public/student/listing.php?id=' . $listingId);
    exit();
}

try {
    $studentStatement = $pdo->prepare('SELECT student_id FROM students WHERE user_id = ? LIMIT 1');
    $studentStatement->execute([(int) $_SESSION['user_id']]);
    $studentId = (int) $studentStatement->fetchColumn();

    if ($studentId <= 0) {
        throw new RuntimeException('Complete your student profile before saving listings.');
    }

    $listingStatement = $pdo->prepare("SELECT listing_id FROM listings WHERE listing_id = ? AND status = 'live'");
    $listingStatement->execute([$listingId]);
    if (!$listingStatement->fetchColumn()) {
        throw new RuntimeException('This listing is no longer available.');
    }

    $saveStatement = $pdo->prepare(
        'INSERT IGNORE INTO saved_listings (student_id, listing_id) VALUES (?, ?)'
    );
    $saveStatement->execute([$studentId, $listingId]);

    $_SESSION['flash']['success'] = $saveStatement->rowCount() === 1
        ? 'Listing saved to your wishlist.'
        : 'Already in your saved listings.';
} catch (RuntimeException $exception) {
    $_SESSION['flash']['error'] = $exception->getMessage();
} catch (PDOException $exception) {
    $_SESSION['flash']['error'] = 'The listing could not be saved right now.';
}

header('Location: /boardnest/public/student/listing.php?id=' . $listingId);
exit();
