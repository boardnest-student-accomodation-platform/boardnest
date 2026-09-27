
<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'landlord'
) {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?status=invalid_request');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$room_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Validate CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !$room_id ||
    !$sessionToken ||
    !is_string($submittedToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    header('Location: dashboard.php?status=error');
    exit();
}

try {
    // Delete only a room owned by the logged-in landlord
    $stmt = $pdo->prepare("
        DELETE r
        FROM rooms r
        INNER JOIN properties p
            ON r.property_id = p.property_id
        INNER JOIN landlords l
            ON p.landlord_id = l.landlord_id
        WHERE r.room_id = ?
          AND l.user_id = ?
    ");

    $stmt->execute([$room_id, $user_id]);

    // Confirm that a room was actually deleted
    if ($stmt->rowCount() !== 1) {
        header('Location: dashboard.php?status=error');
        exit();
    }

    header('Location: dashboard.php?status=room_deleted');
    exit();

} catch (PDOException $e) {
    // Log details privately
    error_log('Delete room error: ' . $e->getMessage());

    // Do not expose database errors to users
    header('Location: dashboard.php?status=error');
    exit();
}