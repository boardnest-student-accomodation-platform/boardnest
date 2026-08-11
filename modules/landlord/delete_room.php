<?php
require_once '../../includes/session.php';
requireRole('landlord');
require_once '../../config/db.php';

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
    $landlord_id = $_SESSION['user_id'];

    // if Room is this Landlord  Property 
    $stmt = $pdo->prepare("
        DELETE r FROM rooms r 
        JOIN properties p ON r.property_id = p.property_id 
        WHERE r.room_id = ? AND p.landlord_id = ?
    ");
    $stmt->execute([$room_id, $landlord_id]);

    header("Location: dashboard.php?status=room_deleted");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>