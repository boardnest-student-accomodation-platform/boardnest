<?php
// Error show in the screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paths
require_once __DIR__ . '/../../../includes/session.php';
requireRole('landlord');
require_once __DIR__ . '/../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $landlord_id   = $_SESSION['user_id'] ?? null;
    $address       = trim($_POST['address'] ?? '');
    $city_id       = $_POST['city_id'] ?? null;
    $maps_url      = trim($_POST['maps_url'] ?? '');
    $latitude      = trim($_POST['latitude'] ?? '');
    $longitude     = trim($_POST['longitude'] ?? '');
    $room_type     = $_POST['room_type'] ?? 'single';
    $price         = $_POST['price'] ?? 0;
    $slot_capacity = $_POST['slot_capacity'] ?? 1;

    if (!$landlord_id) {
        die("Session expired. Please login again.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert Property
        $stmt1 = $pdo->prepare("INSERT INTO properties (landlord_id, city_id, address, maps_url, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt1->execute([$landlord_id, $city_id, $address, $maps_url, $latitude, $longitude]);
        $property_id = $pdo->lastInsertId();

        // 2. Insert Room
        $stmt2 = $pdo->prepare("INSERT INTO rooms (property_id, room_type, price, slot_capacity, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt2->execute([$property_id, $room_type, $price, $slot_capacity]);

        $pdo->commit();

        // Redirect to Dashboard
        header("Location: ../dashboard.php?status=success");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<h3 style='color:red;'>Database Error:</h3> " . $e->getMessage();
        exit();
    }
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>