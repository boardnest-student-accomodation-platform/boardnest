<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';


/* Only allow POST requests */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../views/announcements.php');
    exit();
}


/* Get announcement ID */

$announcementId = (int) ($_POST['announcements_id'] ?? 0);


if ($announcementId <= 0) {

    $_SESSION['error'] = 'Invalid announcement.';

    header('Location: ../views/announcements.php');
    exit();
}


/* Delete announcement */

try {

    $stmt = $pdo->prepare("
        DELETE FROM announcements
        WHERE announcements_id = ?
    ");

    $stmt->execute([$announcementId]);


    if ($stmt->rowCount() === 0) {

        $_SESSION['error'] =
            'Announcement not found or already deleted.';
    } else {

        $_SESSION['success'] =
            'Announcement deleted successfully.';
    }
} catch (PDOException $e) {

    $_SESSION['error'] =
        'The announcement could not be deleted. Please try again.';
}


/* Redirect */

header('Location: ../views/announcements.php');
exit();
