<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


/* Only allow POST requests */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../views/announcements.php');
    exit();
}


/* Get form data */

$announcementId = (int) ($_POST['announcements_id'] ?? 0);
$audience = trim($_POST['audience'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$priority = trim($_POST['priority'] ?? 'normal');


/* Validate announcement ID */

if ($announcementId <= 0) {

    $_SESSION['error'] = 'Invalid announcement.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Validate audience */

$allowedAudiences = [
    'all',
    'student',
    'landlord',
    'field_agent'
];

if (!in_array($audience, $allowedAudiences, true)) {

    $_SESSION['error'] = 'Please select a valid audience.';

    header('Location: ../views/announcements.php?edit=' . $announcementId);
    exit();
}


/* Validate priority */

$allowedPriorities = [
    'normal',
    'urgent'
];

if (!in_array($priority, $allowedPriorities, true)) {

    $_SESSION['error'] = 'Please select a valid priority.';

    header('Location: ../views/announcements.php?edit=' . $announcementId);
    exit();
}


/* Validate subject */

if ($subject === '') {

    $_SESSION['error'] = 'Subject is required.';

    header('Location: ../views/announcements.php?edit=' . $announcementId);
    exit();
}

if (strlen($subject) > 255) {

    $_SESSION['error'] = 'Subject must not exceed 255 characters.';

    header('Location: ../views/announcements.php?edit=' . $announcementId);
    exit();
}


/* Validate message */

if ($message === '') {

    $_SESSION['error'] = 'Message is required.';

    header('Location: ../views/announcements.php?edit=' . $announcementId);
    exit();
}


/* Update announcement */

try {

    $stmt = $pdo->prepare("
        UPDATE announcements
        SET
            audience = ?,
            subject = ?,
            message = ?,
            priority = ?
        WHERE announcements_id = ?
    ");

    $stmt->execute([
        $audience,
        $subject,
        $message,
        $priority,
        $announcementId
    ]);


    $_SESSION['success'] =
        'Announcement updated successfully.';
} catch (PDOException $e) {

    $_SESSION['error'] =
        'The announcement could not be updated. Please try again.';
}


/* Redirect */

header('Location: ../views/announcements.php');
exit();
