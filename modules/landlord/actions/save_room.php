<?php
// Error show in the screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/session.php';
requireRole('landlord');
require_once __DIR__ . '/../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $landlord_id   = $_SESSION['user_id'] ?? null;
    $property_id   = $_POST['property_id'] ?? null;
    $room_type     = $_POST['room_type'] ?? 'single';
    $price         = $_POST['price'] ?? 0;
    $slot_capacity = $_POST['slot_capacity'] ?? 1;

    if (!$landlord_id || !$property_id) {
        die("Invalid Request or Session Expired.");
    }

    try {
        // 1. check property rooms count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM rooms r 
            JOIN properties p ON r.property_id = p.property_id 
            WHERE r.property_id = ? AND p.landlord_id = ?
        ");
        $countStmt->execute([$property_id, $landlord_id]);
        $roomCount = $countStmt->fetchColumn();

        // up to 4, cancel
        if ($roomCount >= 4) {
            die("<h3 style='color:red;'>Free Limit Reached! Maximum 4 rooms allowed per property.</h3><a href='../dashboard.php'>Back to Dashboard</a>");
        }

        // 2. new room Insert databasee
        $stmt = $pdo->prepare("
            INSERT INTO rooms (property_id, room_type, price, slot_capacity, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$property_id, $room_type, $price, $slot_capacity]);

        // after Save Dashboard direct
        header("Location: ../dashboard.php?status=room_added");
        exit();

    } catch (PDOException $e) {
        echo "<h3 style='color:red;'>Database Error:</h3> " . $e->getMessage();
        exit();
    }
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>