<?php
require_once __DIR__ . '/../../includes/session.php';
startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: ../../login.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];
$room_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($room_id) {
    try {
        // IDOR Check: Verify room belongs to property owned by logged in landlord
        $stmt = $pdo->prepare("
            DELETE r FROM rooms r 
            JOIN properties p ON r.property_id = p.property_id 
            JOIN landlords l ON p.landlord_id = l.landlord_id 
            WHERE r.room_id = ? AND l.user_id = ?
        ");
        $stmt->execute([$room_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Delete room error: " . $e->getMessage());
    }
}

header('Location: dashboard.php');
exit();