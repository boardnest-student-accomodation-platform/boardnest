<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/announcements.php');
    exit();
}


/* Get form data */

$audience = trim($_POST['audience'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$priority = trim($_POST['priority'] ?? 'normal');


/* Validate audience */

$allowedAudiences = [
    'all',
    'student',
    'landlord',
    'field_agent'
];

if (!in_array($audience, $allowedAudiences, true)) {

    $_SESSION['error'] = 'Please select a valid audience.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Validate priority */

$allowedPriorities = [
    'normal',
    'urgent'
];

if (!in_array($priority, $allowedPriorities, true)) {

    $_SESSION['error'] = 'Please select a valid priority.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Validate subject */

if ($subject === '') {

    $_SESSION['error'] = 'Subject is required.';

    header('Location: ../views/announcements.php');
    exit();
}

if (strlen($subject) > 255) {

    $_SESSION['error'] = 'Subject must not exceed 255 characters.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Validate message */

if ($message === '') {

    $_SESSION['error'] = 'Message is required.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Get logged-in admin */

$adminId = $_SESSION['user_id'];


/* Get recipients */

if ($audience === 'all') {

    $stmt = $pdo->prepare("
        SELECT user_id, email, role
        FROM users
        WHERE status = 'active'
    ");

    $stmt->execute();
} else {

    $stmt = $pdo->prepare("
        SELECT user_id, email, role
        FROM users
        WHERE role = ?
        AND status = 'active'
    ");

    $stmt->execute([$audience]);
}

$recipients = $stmt->fetchAll();


/* Make sure there are recipients */

if (empty($recipients)) {

    $_SESSION['error'] =
        'There are no active users in the selected audience.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Database transaction */

try {

    $pdo->beginTransaction();


    /* Create announcement */

    $stmt = $pdo->prepare("
        INSERT INTO announcements
        (
            sent_by,
            audience,
            subject,
            message,
            priority
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $adminId,
        $audience,
        $subject,
        $message,
        $priority
    ]);


    /* Send email notification */

    $sentCount = 0;
    $failedCount = 0;

    $notificationStmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, link_url, is_read)
        VALUES (?, 'announcement', ?, 'notifications.php', 0)
    ");

    foreach ($recipients as $recipient) {

        if ($recipient['role'] === 'student') {
            $notificationStmt->execute([
                $recipient['user_id'],
                $subject . ': ' . $message
            ]);
        }

        $email = $recipient['email'];

        $emailSubject = 'BoardNest: ' . $subject;

        $headers = [
            'From: admin@boardnest.lk',
            'Content-Type: text/plain; charset=UTF-8'
        ];

        $emailSent = mail(
            $email,
            $emailSubject,
            $message,
            implode("\r\n", $headers)
        );

        if ($emailSent) {
            $sentCount++;
        } else {
            $failedCount++;
        }
    }


    /* Commit */

    $pdo->commit();


    /* Success message */

    if ($failedCount === 0) {

        $_SESSION['success'] =
            "Announcement sent successfully to {$sentCount} user(s).";
    } else {

        $_SESSION['success'] =
            "Announcement created. {$sentCount} notification(s) sent to users and "
            . "{$failedCount} notification(s) failed.";
    }
} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] =
        'The announcement could not be sent. Please try again.';
}


/* Redirect */

header('Location: ../views/announcements.php');
exit();
